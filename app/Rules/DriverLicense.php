<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DriverLicense implements Rule
{
    public function passes($attribute, $value)
    {
        return strlen($value) >= 10;
    }

    public function message()
    {
        return 'must be a valid drivers license number';
    }
}
