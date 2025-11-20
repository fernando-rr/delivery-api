<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $fillable = [
        'name',
        'contact_phone',
        'slug',
        'domain',
        'db_name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
