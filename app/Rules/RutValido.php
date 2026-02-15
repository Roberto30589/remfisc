<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RutValido implements ValidationRule
{
   public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $rut = preg_replace('/[^0-9kK]/', '', $value);

        if (strlen($rut) < 8 || strlen($rut) > 9) {
            $fail('El :attribute no es un RUT válido.');
            return;
        }

        $dv = strtolower(substr($rut, -1));
        $numero = substr($rut, 0, -1);

        if (!ctype_digit($numero)) {
            $fail('El :attribute no es un RUT válido.');
            return;
        }

        // Evitar RUTs con todos los dígitos iguales
        if (preg_match('/^(\d)\1+$/', $numero)) {
            $fail('El :attribute no es un RUT válido.');
            return;
        }

        $suma = 0;
        $multiplicador = 2;

        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $suma += $numero[$i] * $multiplicador;
            $multiplicador = $multiplicador == 7 ? 2 : $multiplicador + 1;
        }

        $digitoCalculado = 11 - ($suma % 11);

        $digitoCalculado = match ($digitoCalculado) {
            11 => '0',
            10 => 'k',
            default => (string) $digitoCalculado,
        };

        if ($dv !== $digitoCalculado) {
            $fail('El :attribute no es un RUT válido.');
        }
    }

}
