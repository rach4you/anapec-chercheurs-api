<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WebServiceDomain extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'api_web_service_domains';

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
        'sort_order',
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
            'sort_order' => 'integer',
        ];
    }

    /**
     * The web services associated with this domain.
     *
     * @return BelongsToMany
     */
    public function webServices()
    {
        return $this->belongsToMany(
            WebService::class,
            'api_domain_web_services',
            'domain_id',
            'web_service_id'
        )->using(DomainWebService::class)->withTimestamps();
    }

    /**
     * The users that have a permission record for this domain.
     *
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'api_user_domains',
            'domain_id',
            'user_id'
        )->using(UserDomain::class)->withPivot('is_enabled')->withTimestamps();
    }

    /**
     * Determine whether the domain is active.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}
