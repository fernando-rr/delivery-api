<?php

namespace App\DTOs;

use Illuminate\Support\Collection;

class RestaurantUpdateDTO extends BaseDTO
{
    public static function getFillableAttributes(): Collection
    {
        return parent::getFillableAttributes()->merge([
            'id' => 'required|integer|exists:restaurants,id',
            'name' => 'sometimes|string|max:255',
            'contact_phone' => 'sometimes|string|max:20',
            'slug' => 'sometimes|string|max:255',
            'domain' => 'sometimes|nullable|string|max:255',
            'db_name' => 'sometimes|string|max:255',
            'active' => 'sometimes|boolean',
        ]);
    }
}
