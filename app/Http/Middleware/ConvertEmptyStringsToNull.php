<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull as BaseConverter;

class ConvertEmptyStringsToNull extends BaseConverter
{
    /**
     * The names of the attributes that should not be converted.
     *
     * @var array
     */
    protected $except = [
        'code',
    ];

    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        if (in_array($key, $this->except, true)) {
            return $value;
        }

        return is_string($value) && $value === '' ? null : $value;
    }
}
