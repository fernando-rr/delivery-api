<?php

namespace App\DTOs\Casts;

use App\Contracts\DTOs\BaseCast;
use Illuminate\Support\Carbon;

class DateCast implements BaseCast
{
    /**
     * Converte o valor para o tipo apropriado ao setar no DTO (string -> Carbon)
     */
    public static function set(mixed $value): mixed
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        return new Carbon($value);
    }

    /**
     * Converte o valor para o tipo apropriado ao pegar do DTO (Carbon -> string)
     */
    public static function get(mixed $value): mixed
    {
        if (is_string($value)) {
            return $value;
        }

        return $value->toDateTimeString();
    }
}