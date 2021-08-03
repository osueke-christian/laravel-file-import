<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Services\User\Contracts\UserServiceInterface;
use App\Services\Import\Contracts\FileParserInterface;

class ProcessFileChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var UserServiceInterface
     */
    private $userService;

    /**
     * @var string $chunk
     */
    private $chunk;

    /**
     * @var $fileParser
     */
    private $fileParser;

    /**
     * Create a new job instance.
     *
     * @param string $chunk
     * @param FileParserInterface $fileParser
     * @param UserServiceInterface $userService
     * @return void
     */
    public function __construct(string $chunk, FileParserInterface $fileParser, UserServiceInterface $userService)
    {
        $this->chunk = $chunk;
        $this->fileParser = $fileParser;
        $this->userService = $userService;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->fileParser->toArray( $this->chunk ) as $user){
            $this->userService->save($user);
        }
    }
}
