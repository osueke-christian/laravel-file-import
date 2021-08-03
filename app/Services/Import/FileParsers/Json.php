<?php

namespace App\Services\Import\FileParsers;

use App\Services\Import\Contracts\FileParserInterface;

class Json implements FileParserInterface
{
    /**
     * @var string buffer 
     */
    private static $buffer;

    /**
     * Configuration to be used for file import
     * 
     * @var array $config
     */
    private $config;

    /**
     * construct class dependencies
     */
    public function __construct()
    {
        $this->config = $this->loadConfig();
        $this->restoreBuffer();
    }

    /**
     * Loads config in memory
     */
    public function loadConfig(): array
    {
        return config('custom.fileparser');
    }

    /**
     * returns name of the extension supported by a file parser
     * 
     * @return string
     */
    public function getSupportedExtension(): string
    {
        return 'csv';
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
        self::$buffer.=$chunk;
        $splitChunk = mb_str_split(self::$buffer);

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
                    self::$buffer, 
                    $startJsonPosition, 
                    $endJsonPosition - $startJsonPosition + 1
                ), true );

                // reset trackers
                self::$buffer = mb_substr(self::$buffer, $endJsonPosition+1);
                $splitChunk = mb_str_split(self::$buffer);
                $currentPosition = 0;

                $startJson = '';
                $endJson = '';

                yield $jsonBlock;
            }else{
                $currentPosition+=1;
            }
        }

        $this->saveBuffer();
    }

    /**
     * checks if we had already started parsing file before
     * and resume from the left off buffer
     * 
     * @return self
     */
    public function saveBuffer(): self
    {
        file_put_contents($this->config['buffer_tracker'], self::$buffer);

        return $this;
    }

    /**
     * check if we had already started parsing file before
     * and resume from the left off buffer
     * 
     * @return self
     */
    public function restoreBuffer(): self
    {
        self::$buffer = file_get_contents($this->config['buffer_tracker']);

        return $this;
    }
}