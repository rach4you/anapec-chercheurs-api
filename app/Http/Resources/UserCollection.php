<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * The model that this resource collects.
     *
     * @var string
     */
    public $collects = UserResource::class;
}
