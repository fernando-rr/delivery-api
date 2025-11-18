<?php

namespace App\Contracts\DTOs;

interface BaseCast
{
    public static function set(mixed $value): mixed;
    public static function get(mixed $value): mixed;
}