<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Admin "Utilisateurs" list collection.
 *
 * Same shape as the legacy {@see UserCollection} (a plain array under
 * `data`) so existing clients keep working, but each item is transformed
 * by {@see AdminUserResource} which adds the derived access indicators
 * used by the admin table.
 */
class AdminUserCollection extends ResourceCollection
{
    /**
     * The resource that this collection returns.
     *
     * @var string
     */
    public $collects = AdminUserResource::class;
}
