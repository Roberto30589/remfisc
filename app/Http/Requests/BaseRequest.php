<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequest extends FormRequest
{
    /**
     * Autoriza la request.
     * Puedes sobreescribirlo en hijos si necesitas lógica por usuario.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mensajes de validación globales del sistema.
     * Se pueden sobrescribir en requests hijas.
     */
    public function messages(): array
    {
        return [
            'required'  => 'El campo :attribute es obligatorio.',
            'string'    => 'El campo :attribute debe ser texto.',
            'integer'   => 'El campo :attribute debe ser un número válido.',
            'numeric'   => 'El campo :attribute debe ser numérico.',
            'array'     => 'El campo :attribute debe ser una lista válida.',
            'min'       => 'El campo :attribute debe tener al menos :min.',
            'max'       => 'El campo :attribute no debe superar los :max.',
            'email'     => 'El correo electrónico no es válido.',
            'unique'    => 'El :attribute ya está registrado.',
            'exists'    => 'El :attribute seleccionado no es válido.',
            'confirmed' => 'La confirmación de :attribute no coincide.',
            'boolean'   => 'El campo :attribute debe ser verdadero o falso.',
        ];
    }

    /**
     * Hook antes de validar.
     * Ideal para sanitizar datos.
     */
    protected function prepareForValidation(): void
    {
        // Ejemplo global:
        // Limpiar espacios en todos los strings
        $this->merge(
            collect($this->all())
                ->map(function ($value) {
                    return is_string($value) ? trim($value) : $value;
                })
                ->toArray()
        );
    }

    /**
     * Hook después de validar correctamente.
     */
    protected function passedValidation(): void
    {
        // Aquí puedes transformar datos si lo necesitas
        // Ejemplo:
        // $this->replace([
        //     'email' => strtolower($this->email),
        // ]);
    }
}
