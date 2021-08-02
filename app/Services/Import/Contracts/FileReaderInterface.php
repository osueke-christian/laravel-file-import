<?php

namespace App\Services\Import\Contracts;

interface FileReaderInterface
{
    /**
     * read file content in chunks 
     * 
     * @param string $filePath
     * @param int $chunkSize
     * @return \Generator
     */
    public function lazyLoad(string $filePath, int $chunkSize): \Generator;

    /**
     * checks if we had already started reading file before
     * and resume from the line we stopped
     * 
     * @return void
     */
    public function resume(): void;

    /**
     * extracts valid json from streamed chunk and convert to array
     * 
     * @param string $chunk
     * @return \Generator
     */
    public function toArray( $chunk ): \Generator;

    /**
     * returns name of the extension supported by a file reader
     */
    public function getSupportedExtension(): string;

    /**
     * Closes file if open
     * 
     * @return void
     */
    public function closeFile(): void;
}