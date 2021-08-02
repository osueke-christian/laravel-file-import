<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Import\Contracts\ImportServiceInterface;

class Import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import {filePath?}';

    /**
     * Service to be used to imports
     */
    private $importService;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import content of file into a database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ImportServiceInterface $importService)
    {
        parent::__construct();
        $this->importService = $importService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filePath = $this->argument('filePath') ?? null;
        $this->importService->import($filePath);

        return true;
    }
}
