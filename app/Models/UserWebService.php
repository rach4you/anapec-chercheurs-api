<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserWebService extends Pivot
{
    /**
     * The table associated with the pivot model.
     *
     * @var string
     */
    protected $table = 'api_user_web_services';

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
        'web_service_id',
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
     * The Web Service associated with this permission record.
     */
    public function webService()
    {
        return $this->belongsTo(WebService::class, 'web_service_id');
    }

    /**
     * The User associated with this permission record.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
