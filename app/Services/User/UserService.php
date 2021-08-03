<?php

namespace App\Services\User;

use DB;
use Carbon\Carbon;
use App\Models\User;
use App\Services\User\Contracts\FileParserInterface;
use App\Services\User\Contracts\UserServiceInterface;

class UserService implements UserServiceInterface
{
    /**
     * @var User $user
     */
    private $user;

    /**
     * construct class dependencies
     * 
     * @param User user
     */
    public function __construct( User $user )
    {
        $this->user = $user;
    }

    /**
     * apply age constraints to filter data
     * 
     * @param array $record
     * @return bool
     */
    // TODO: as a suggested improvement, consider using pipelines
    public function isRequiredAge(array $record): bool
    {
        $dob = null;
        
        // data with unknown dob should be stored
        if(empty($record['date_of_birth'])){
            return true;
        }

        // cater for dob in the format dd/mm/yyyy
        elseif( strlen($record['date_of_birth']) === strlen('dd/mm/yyyy') ){
            $dob = Carbon::createFromFormat('d/m/Y', $record['date_of_birth']);
        }
        // cater for dob in epoc and other formats
        else{
            $dob = Carbon::parse($record['date_of_birth']);
        }

        // get age from date difference
        $age = $dob->diffInYears( Carbon::now() );

        // only select age between 18 and 65
        if( $age >= 18 && $age <= 65){
            return true;
        }

        // age is not in required range return false
        return false;
    }

    /**
     * Store valid json gotten so far from chunks to db
     * 
    NOTE: 
    A faster method will be to build a chunk of array and
    insert all at once using Model::insert()
    but this does not accomodate table relations
    hence the choice to insert one by one
     * @param array $user
     * @return bool
     */
    public function save( array $userRecord ): bool
    {
        // filter records where age is between 18 and 16 or unknown
        if(! $this->isRequiredAge($userRecord) ){
            return false;
        }

        DB::beginTransaction();
        try{
            $user = $this->user::create($userRecord);
            $user->card()->create($userRecord['credit_card']);

            DB::commit();
        }
        catch(\Throwable $error){
            DB::rollback();
            throw $error;
        }

        return true;
    }
}