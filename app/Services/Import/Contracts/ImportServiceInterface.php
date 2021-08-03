<?php

namespace App\Services\Import\Contracts;

use App\Services\Import\Contracts\FileParserInterface;

interface ImportServiceInterface
{
    /**
     * import file content
     * 
     * @param string $filePath = null
     * @return string
     */
    public function import( string $filePath = null );

    /**
     * format file path and confirm file exists
     * 
     * @param string $filePath = null
     * @return string
     */
    public function resolvePath(string $filePath = null): string;

    /**
     * determine which file parser to use based off file extension
     * 
     * @param string $extension
     * @return self
     */
    public function setFileParser(string $extention): self;

    /**
     * Get the file parser instance been used for file import
     * 
     * @return FileParserInterface
     */
    public function getFileParser(): FileParserInterface;

    /**
     * checks if we had already started reading file before
     * and resume from the line we stopped
     * 
     * @return self
     */
    public function openFile(string $filePath): self;

    /**
     * breaks file in chunks using generators
     * to ensure memory efficiency
     * 
     * @param int chunkSize
     * @return \Generator
     */
    public function lazyLoad(int $chunkSize): \Generator;
    
    /**
     * save current read position in file 
     * 
     * @return self
     */
    public function saveReadState(): self;

    /**
     * checks if we had already started reading file before
     * and resume from the line we stopped
     * 
     * @return self
     */
    public function resumeReadState(): self;

    /**
     * Closes file if open
     * 
     * @return bool
     */
    public function closeFile(): bool;
}