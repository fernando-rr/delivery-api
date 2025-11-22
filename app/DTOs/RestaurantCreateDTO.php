<?php

namespace App\DTOs;

use Illuminate\Support\Collection;

class RestaurantCreateDTO extends BaseDTO
{
    public static function getFillableAttributes(): Collection
    {
        return collect([
            'name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'slug' => 'required|string|max:255|unique:restaurants,slug',
            'domain' => 'nullable|string|max:255|unique:restaurants,domain',
            'active' => 'nullable|boolean',
        ]);
    }
}
