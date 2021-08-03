<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Read Position Trackers
    |--------------------------------------------------------------------------
    |
    | Path to files to be used for tracking upload, 
    | relative to project root, in case of interuptions
    | 
    | Note:
    | =====
    | We could have used a single file, serializing and array/object 
    | of both trackers and unserializing when needed, however, this
    | would be a quicker
    |
    */
    'position_tracker' => base_path().'/resources/challenge/position_tracker.txt',
    'buffer_tracker' => base_path().'/resources/challenge/buffer_tracker.txt',

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