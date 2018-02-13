<?php namespace App\Models\UserLocation;

use App\Models\UserLocation\Traits\Attribute\Attribute;
use App\Models\UserLocation\Traits\Relationship\Relationship;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserLocation.
 */
class UserLocation extends Model
{
    use Attribute,
        Relationship;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_locations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'address_name',
        'address',
        'other_details',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'other_details'
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}
