<?php

namespace App\DTOs;

use Illuminate\Support\Collection;

class LoginDTO extends BaseDTO
{
    public static function getFillableAttributes(): Collection
    {
        return collect([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
    }
}
