<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterServiceRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'name' => 'required|string|max:20|unique:services,name',
            'price' => 'required|integer|min:0',
            'description' => 'nullable|string|max:150',
            'duration' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del servicio es obligatorio.',
            'name.string' => 'El nombre del servicio debe ser una cadena de texto.',
            'name.max' => 'El nombre del servicio no puede tener más de 20 caracteres.',
            'name.unique' => 'Ya existe un servicio con ese nombre.',
            'price.integer' => 'El precio debe ser un número entero.',
            'price.min' => 'El precio no puede ser menor que 0.',
            'price.required' => 'El precio es obligatorio.',
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción no puede tener más de 150 caracteres.',
            'duration.integer' => 'La duración debe ser un número entero.',
            'duration.min' => 'La duración no puede ser menor que 0.',
        ];
    }   
}
