<?php

namespace App\DTOs;

use Illuminate\Support\Collection;

class RestaurantDTO extends BaseDTO
{
    public static function getFillableAttributes(): Collection
    {
        return parent::getFillableAttributes()->merge([
            'name' => 'string',
            'contact_phone' => 'string',
            'slug' => 'string',
            'domain' => 'nullable|string',
            'db_name' => 'string',
            'active' => 'boolean',
        ]);
    }
}
