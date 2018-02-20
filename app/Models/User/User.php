<?php

namespace App\Models\User;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\User\Traits\Attribute\Attribute;
use App\Models\User\Traits\Relationship\Relationship;

/**
 * Class User.
 */
class User extends Authenticatable
{
    use Notifiable,
        Attribute,
        Relationship;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'phone',
        'code',
        'type',
        'status',
        'is_admin',
        'family_code',
        'chatroom_id',
        'email',
        'password',
        'device_type',
        'device_token',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token'
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}
