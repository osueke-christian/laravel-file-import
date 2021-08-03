<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Read Position Tracker
    |--------------------------------------------------------------------------
    |
    | Path to file to be used for tracking upload, 
    |relative to project root
    |
    */
    'tracker' => base_path().'/resources/challenge/tracker.txt',

    /*
    |--------------------------------------------------------------------------
    | Testing Import File Path
    |--------------------------------------------------------------------------
    |
    | Path to file to be used for testing import
    | Path is relative to project root directory
    |
    */
    'testImportFilePath' => '/resources/challenge/test.json',

    /*
    |--------------------------------------------------------------------------
    | Chunk Size
    |--------------------------------------------------------------------------
    |
    | how many bytes of file to read per chunk
    |
    */
    'CHUNK_SIZE' => 8192,

    /*
    |--------------------------------------------------------------------------
    | Class Maps
    |--------------------------------------------------------------------------
    |
    | This is the array of Classes that maps to file parsers.
    | more parsers can be added here, however ensure the parser class
    | implements \App\Services\Import\Contracts\FileParserInterface
    |
    */
    'map' => [
        'csv' => \App\Services\Import\FileParsers\Csv::class,
        'xml' => \App\Services\Import\FileParsers\Xml::class,
        'json' => \App\Services\Import\FileParsers\Json::class,
        // ...
    ],
];