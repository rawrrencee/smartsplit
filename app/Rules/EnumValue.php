<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EnumValue implements ValidationRule
{
    private $enumCases;

    public function __construct(array $enumCases)
    {
        $this->enumCases = $enumCases;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array($value, array_column($this->enumCases, 'value'))) {
            $fail('The value provided was not found in the list of accepted parameters.');
        }
    }
}
