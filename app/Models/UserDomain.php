<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserDomain extends Pivot
{
    /**
     * The table associated with the pivot model.
     *
     * @var string
     */
    protected $table = 'api_user_domains';

    /**
     * The "type" of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs should be incremented.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'domain_id',
        'is_enabled',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    /**
     * The Web Service Domain associated with this permission record.
     */
    public function domain()
    {
        return $this->belongsTo(WebServiceDomain::class, 'domain_id');
    }

    /**
     * The User associated with this permission record.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
