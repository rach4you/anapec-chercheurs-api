<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DomainWebService extends Pivot
{
    /**
     * The table associated with the pivot model.
     *
     * @var string
     */
    protected $table = 'api_domain_web_services';

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
        'domain_id',
        'web_service_id',
    ];

    /**
     * The Web Service associated with this domain membership.
     */
    public function webService()
    {
        return $this->belongsTo(WebService::class, 'web_service_id');
    }

    /**
     * The Web Service Domain associated with this membership.
     */
    public function domain()
    {
        return $this->belongsTo(WebServiceDomain::class, 'domain_id');
    }
}
