<?php

namespace App\Services\Import;

use DB;
use Carbon\Carbon;
use App\Models\User;
use App\Services\Import\Contracts\FileReaderInterface;
use App\Services\Import\Contracts\ImportServiceInterface;

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
     * @var FileReaderInterface $fileReader
     */
    private $fileReader;

    /**
     * @var User $user
     */
    private $user;

    /**
     * @var string buffer
     */
    private $buffer;

    /**
     * construct class dependencies
     * 
     * @param FileReaderInterface fileReader
     * @param User user
     */
    public function __construct( User $user )
    {
        $this->loadConfig();
        $this->user = $user;
    }

    /**
     * Loads config in memory
     */
    public function loadConfig(): void
    {
        $this->config = config('custom.filereader');
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

        // set file reader based off file extension type
        $this->setFileReader( pathinfo($filePath, PATHINFO_EXTENSION) );
        
        // read file in chunks
        // note: lazyload uses generators for memory efficiency
        foreach ($this->getFileReader()->lazyLoad( $filePath, $this->config['CHUNK_SIZE'] ) as $user){
            // filter records where age is between 18 and 16 or unknown
            if( $this->isRequiredAge($user) ){
                // store data
                $this->save($user);
            }
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
     * determine which file reader to use based off file extension
     */
    // TODO: use a custom exception handler
    public function setFileReader(string $extention): self
    {
        // if file reader for specified extension has already been set, return
        if($this->fileReader && $this->fileReader->getSupportedExtension() === $extention){
            return $this;
        }

        // ensure there is a reader configured for specified file extension 
        if ( empty($this->config['map'][$extention]) ) {
            throw new \InvalidArgumentException('File extension reader not set in config file.');
        }

        // ensure the configured file reader class is valid
        if (! class_exists($this->config['map'][$extention])) {
            throw new \InvalidArgumentException('File extension reader not found. Update config map.');
        }

        // reflect on the clas and ensure it implements the required interface
        $reflect = new \ReflectionClass($this->config['map'][$extention]);
        if (! $reflect->implementsInterface(FileReaderInterface::class)) {
            throw new \InvalidArgumentException("File reader must be an instance of FileReaderInterface.");
        }

        // finally set the fileReader
        $class = $this->config['map'][$extention];
        $this->fileReader = new $class( $this->config );

        return $this;
    }

    /**
     * Get the file reader instance been used for file import
     */
    public function getFileReader()
    {
        return $this->fileReader;
    }

    /**
     * apply age constraints to filter data
     * 
     * @param array $record
     * @return bool
     */
    // TODO: as a suggested improvement, consider using pipelines
    public function isRequiredAge(array $record): bool
    {
        $dob = null;
        
        // data with unknown dob should be stored
        if(empty($record['date_of_birth'])){
            return true;
        }

        // cater for dob in the format dd/mm/yyyy
        elseif( strlen($record['date_of_birth']) === strlen('dd/mm/yyyy') ){
            $dob = Carbon::createFromFormat('d/m/Y', $record['date_of_birth']);
        }
        // cater for dob in epoc and other formats
        else{
            $dob = Carbon::parse($record['date_of_birth']);
        }

        // get age from date difference
        $age = $dob->diffInYears( Carbon::now() );

        // only select age between 18 and 65
        if( $age >= 18 && $age <= 65){
            return true;
        }

        // age is not in required range return false
        return false;
    }

    /**
     * Store valid json gotten so far from chunks to db
     * 
     * @param array $user
     * @return bool
     */
    public function save( array $userRecord ): bool
    {
        // NOTE: 
        // A faster method will be to build a chunk of array and
        // insert all at once using Model::insert()
        // but this does not accomodate table relations
        // hence the choice to insert one by one
        DB::beginTransaction();
        try{
            $user = $this->user::create($userRecord);
            $user->card()->create($userRecord['credit_card']);

            DB::commit();
        }
        catch(\Throwable $error){
            DB::rollback();
            throw $error;
        }

        return true;
    }
}