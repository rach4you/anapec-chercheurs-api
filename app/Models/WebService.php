<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebService extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'api_web_services';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * The users associated with this Web Service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'api_user_web_services',
            'web_service_id',
            'user_id'
        )->using(UserWebService::class)->withPivot('is_enabled')->withTimestamps();
    }
}
