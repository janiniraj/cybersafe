<?php namespace App\Models\Emergency;

use App\Models\Emergency\Traits\Attribute\Attribute;
use App\Models\Emergency\Traits\Relationship\Relationship;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Emergency.
 */
class Emergency extends Model
{
    use Attribute,
        Relationship;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'emergencies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'dismiss',
        'dismissed_by',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}
