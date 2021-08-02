<?php

namespace App\Services\Import\FileReaders;

use App\Services\Import\Contracts\FileReaderInterface;

class Json implements FileReaderInterface
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
     * @var string buffer 
     */
    private $buffer;

    /**
     * @var int lastReadPosition 
     */
    private $lastReadPosition;

    public function __construct( array $config )
    {
        // TODO: validate config
        $this->config = $config;
    }

    /**
     * read file content in chunks 
     * 
     * @param string $filePath
     * @param int $chunkSize
     * @return \Generator
     */
    public function lazyLoad(string $filePath, int $chunkSize): \Generator
    {
        // open file to be imported
        $this->file = fopen($filePath, 'r');

        // continue from last read position if initially read
        $this->resume();
        
        // recursively read file in chunks till we get to end of file
        while(!feof($this->file)){
            // grab a chunk of data and pass for processing
            $chunk = fread($this->file, $chunkSize);

            // convert chunk to array
            // which uses generators to process chunk for memory efficiency
            yield from $this->toArray($chunk);

            // track last line read incase of system failure
            file_put_contents($this->config['tracker'], (int)ftell($this->file));
        }

        $this->closeFile();
    }

    /**
     * check if we had already started reading file before
     * and resume from the line we stopped
     * 
     * @return void
     */
    public function resume(): void
    {
        // determine the last line read and resume import
        $this->lastReadPosition = (int)file_get_contents($this->config['tracker']);

        // move pointer to last read position
        fseek($this->file, $this->lastReadPosition);
    }
    
    /**
     * extract valid json from streamed chunk and convert to array
     * 
     * @param string $chunk
     * @return \Generator
     */
    public function toArray( $chunk ): \Generator
    {
        $startJson = '';
        $endJson = '';
        $jsonBlock = '';
        $startJsonPosition = 0;
        $endJsonPosition = 0;
        $validJsonToStore = [];

        // parse json
        $this->buffer.=$chunk;
        $splitChunk = mb_str_split($this->buffer);

        // iterate through each character in chunk
        // extract valid jsons and convert them to array
        $currentPosition = 0;
        while( $currentPosition < count($splitChunk) ){
            $character = $splitChunk[$currentPosition];

            // if character is an open bracket then its probably the begining of json object
            if($character === '{'){
                if($startJson === ''){
                    $startJsonPosition = $currentPosition;
                }
                
                $startJson.=$character;
            }

            // if character is a close bracket then its probably the end of json object
            if($character === '}'){
                $endJson.=$character;
                $endJsonPosition = $currentPosition;
            }
            
            // so check if we have seen equal number of open and closed brackets so far
            // to validate we have a json block, then convert json to array
            if($startJson !== '' && strlen($startJson) === strlen($endJson)){
                // yeild valid json block
                $jsonBlock = json_decode( mb_substr(
                    $this->buffer, 
                    $startJsonPosition, 
                    $endJsonPosition - $startJsonPosition + 1
                ), true );

                // reset trackers
                $this->buffer = mb_substr($this->buffer, $endJsonPosition+1);
                $splitChunk = mb_str_split($this->buffer);
                $currentPosition = 0;

                $startJson = '';
                $endJson = '';

                yield $jsonBlock;
            }else{
                $currentPosition+=1;
            }
        }
    }

    /**
     * Close file if open
     * 
     * @return void
     */
    public function closeFile(): void
    {
        if( is_resource($this->file) ){
            fclose($this->file);
        }
    }

    /**
     * returns name of the extension supported by a file reader
     */
    public function getSupportedExtension(): string
    {
        return 'csv';
    }

    /**
     * Ensure file close incase we forget to do so
     */
    public function __destruct()
    {
        $this->closeFile();
    }
}