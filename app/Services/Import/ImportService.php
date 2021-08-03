<?php

namespace App\Services\Import;

use DB;
use Carbon\Carbon;
use App\Models\User;
use App\Jobs\ProcessFileChunk;
use App\Services\Import\Contracts\FileParserInterface;
use App\Services\Import\Contracts\ImportServiceInterface;
use App\Services\User\Contracts\UserServiceInterface;

class ImportService implements ImportServiceInterface
{
    /**
     * Configuration to be used for file import
     */
    private $config;

    /**
     * @var resource file 
     */
    private $file;

    /**
     * @var UserServiceInterface userService
     */
    private $userService;

    /**
     * @var FileParserInterface $fileParser
     */
    private $fileParser;

    /**
     * @var string buffer
     */
    private $buffer;

    /**
     * construct class dependencies
     * 
     * @param FileParserInterface fileParser
     * @param User user
     */
    public function __construct(UserServiceInterface $userService)
    {
        $this->loadConfig();
        $this->userService = $userService;
    }

    /**
     * Loads config in memory
     */
    public function loadConfig(): void
    {
        $this->config = config('custom.fileparser');
    }

    public function openFile(string $filePath): self
    {
        $this->file = fopen($filePath, 'r');

        return $this;
    }

    /**
     * import file content
     * 
     * @param string $filePath = null
     * @return string
     */
    public function import( string $filePath = null )
    {
        // resolve and validate path to import file
        $filePath = $this->resolvePath($filePath);
        
        $this 
            ->setFileParser( pathinfo($filePath, PATHINFO_EXTENSION) ) // set file parser
            ->openFile($filePath) // open file to be imported
            ->resumeReadState(); // continue from last read position if initially read
                    
        // load file content in chunks and pass to job for processing
        foreach($this->lazyLoad($this->config['CHUNK_SIZE']) as $chunk){
            ProcessFileChunk::dispatch($chunk, $this->getFileParser(), $this->userService)
                            ->delay( now()->addSeconds(10) );
        };

        // close file
        $this->closeFile();
    }

    /**
     * breaks file in chunks using generators
     * to ensure memory efficiency
     * 
     * @param int chunkSize
     * @return \Generator
     */
    public function lazyLoad(int $chunkSize): \Generator
    {
        // recursively read file in chunks till we get to end of file
        while(!feof($this->file)){
            // grab a chunk of data and pass for processing
            yield fread($this->file, $chunkSize);

            // keep track of read position
            $this->saveReadState();
        }
    }

    /**
     * format file path and confirm file exists
     * 
     * @param string $filePath = null
     * @return string
     */
    public function resolvePath(string $filePath = null): string
    {
        $filePath = base_path().'/'.($filePath ?? $this->config['testImportFilePath']);
        $filePath = preg_replace('#/+#','/', $filePath);

        if(! file_exists($filePath)){
            throw new \InvalidArgumentException('Invalid file path');
        }
        
        return $filePath;
    }

    /**
     * determine which file parser to use based off file extension
     */
    // TODO: use a custom exception handler
    public function setFileParser(string $extention): self
    {
        // if file parser for specified extension has already been set, return
        if($this->fileParser && $this->fileParser->getSupportedExtension() === $extention){
            return $this;
        }

        // ensure there is a parser configured for specified file extension 
        if ( empty($this->config['map'][$extention]) ) {
            throw new \InvalidArgumentException('File extension parser not set in config file.');
        }

        // ensure the configured file parser class is valid
        if (! class_exists($this->config['map'][$extention])) {
            throw new \InvalidArgumentException('File extension parser not found. Update config map.');
        }

        // reflect on the clas and ensure it implements the required interface
        $reflect = new \ReflectionClass($this->config['map'][$extention]);
        if (! $reflect->implementsInterface(FileParserInterface::class)) {
            throw new \InvalidArgumentException("File parser must be an instance of FileParserInterface.");
        }

        // finally set the fileParser
        $class = $this->config['map'][$extention];
        $this->fileParser = new $class( $this->config );

        return $this;
    }

    /**
     * Get the file parser instance been used for file import
     */
    public function getFileParser(): FileParserInterface
    {
        return $this->fileParser;
    }

    /**
     * checks if we had already started reading file before
     * and resume from the line we stopped
     * 
     * @return self
     */
    public function saveReadState(): self
    {
        // keep track of last line read incase of system failure
        file_put_contents($this->config['tracker'], (int)ftell($this->file));

        return $this;
    }

    /**
     * check if we had already started reading file before
     * and resume from the line we stopped
     * 
     * @return self
     */
    public function resumeReadState(): self
    {
        // determine the last line read and resume import
        $this->lastReadPosition = (int)file_get_contents($this->config['tracker']);

        // move pointer to last read position
        fseek($this->file, $this->lastReadPosition);

        return $this;
    }

    /**
     * Close file if open
     * 
     * @return void
     */
    public function closeFile(): bool
    {
        if( is_resource($this->file) ){
            fclose($this->file);
        }

        return true;
    }

    /**
     * Ensure file close incase we forget to do so
     */
    public function __destruct()
    {
        $this->closeFile();
    }
}