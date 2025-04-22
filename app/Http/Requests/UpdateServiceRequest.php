<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends ApiFormRequest
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
        $serviceId = $this->route('id'); 
        return [
            'name' => 'sometimes|string|max:20|unique:services,name,' . $serviceId,
            'price' => 'sometimes|integer|min:0',
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
            'name.string' => 'El nombre del servicio debe ser una cadena de texto.',
            'name.max' => 'El nombre del servicio no puede tener más de 20 caracteres.',
            'name.unique' => 'Ya existe un servicio con ese nombre.',
            'price.integer' => 'El precio debe ser un número entero.',
            'price.min' => 'El precio no puede ser menor que 0.',
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción no puede tener más de 150 caracteres.',
            'duration.integer' => 'La duración debe ser un número entero.',
            'duration.min' => 'La duración no puede ser menor que 0.',
        ];
    }

}
