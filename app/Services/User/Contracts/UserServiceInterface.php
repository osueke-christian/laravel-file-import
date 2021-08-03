<?php

namespace App\Services\User\Contracts;

interface UserServiceInterface
{
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