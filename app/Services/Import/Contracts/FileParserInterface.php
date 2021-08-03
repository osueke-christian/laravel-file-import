<?php

namespace App\Services\Import\Contracts;

interface FileParserInterface
{
    /**
     * extracts valid json from streamed chunk and convert to array
     * 
     * @param string $chunk
     * @return \Generator
     */
    public function toArray( $chunk ): \Generator;

    /**
     * returns name of the extension supported by a file parser
     * 
     * @return string
     */
    public function getSupportedExtension(): string;
}