<?php

namespace App\Services\Import\Contracts;

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
     * determine which file reader to use based off file extension
     */
    public function setFileReader(string $extention): self;

    /**
     * Get the file reader instance been used for file import
     */
    public function getFileReader();

    /**
     * apply age constraints to filter data
     * 
     * @param array $record
     * @return bool
     */
    // TODO: as a suggested improvement, consider using pipelines
    public function isRequiredAge(array $record): bool;

    /**
     * Store valid json gotten so far from chunks to db
     * 
     * @param array $user
     * @return bool
     */
    public function save( array $user ): bool;
}