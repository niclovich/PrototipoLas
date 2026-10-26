<?php

namespace App\Actions\Fortify;

use App\Rules\ContraseniaSegura;
use Illuminate\Contracts\Validation\Rule;

trait PasswordValidationRules
{
    /**
     * @return array<int, Rule|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', new ContraseniaSegura, 'confirmed'];
    }
}
