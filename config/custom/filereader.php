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
    'testImportFilePath' => '/challenge/test.json',

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
    | This is the array of Classes that maps to file readers.
    | more readers can be added here, however ensure the reader class
    | implements \App\Services\Import\Contracts\FileReaderInterface
    |
    */
    'map' => [
        'csv' => \App\Services\Import\FileReaders\Csv::class,
        'xml' => \App\Services\Import\FileReaders\Xml::class,
        'json' => \App\Services\Import\FileReaders\Json::class,
        // ...
    ],
];