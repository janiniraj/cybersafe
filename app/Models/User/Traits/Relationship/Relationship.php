<?php namespace App\Models\User\Traits\Relationship;

use App\Models\UserAddress\UserAddress;
use App\Models\UserLocation\UserLocation;

/**
 * Class Relationship.
 */
trait Relationship
{
    /**
     * @return mixed
     */
    public function locations()
    {
        return $this->hasMany(UserLocation::class, 'user_id');
    }

    /**
     * @return mixed
     */
    public function address()
    {
        return $this->hasOne(UserAddress::class, 'user_id');
    }
}
