<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends ApiFormRequest
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
            'name' => 'sometimes|string|min:3|max:50',
            'nickname' => 'sometimes|string|min:3|max:15',
            'phone' => 'sometimes|string|min:10|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre no debe exceder los 50 caracteres.',
            'nickname.string' => 'El apodo debe ser una cadena de texto.',
            'nickname.min' => 'El apodo debe tener al menos 3 caracteres.',
            'nickname.max' => 'El apodo no debe exceder los 15 caracteres.',
            'phone.string' => 'El teléfono debe ser una cadena de texto.',
            'phone.min' => 'El teléfono debe tener exactamente 10 caracteres.',
            'phone.max' => 'El teléfono debe tener exactamente 10 caracteres.',
        ];
    }
}
