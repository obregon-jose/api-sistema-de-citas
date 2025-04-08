<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecoverPasswordRequest extends ApiFormRequest
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
            'email' => 'required|email|exists:users,email',
            'code' => 'sometimes|exists:password_reset_tokens,code',
            'password' => 'sometimes|min:8|regex:/[A-Z]/|regex:/[0-9]/',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El campo correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.exists' => 'No hemos encontrado una cuenta asociada a este correo.',
            'code.required' => 'El campo código es obligatorio.',
            'code.string' => 'El código debe ser una cadena de texto.',
            'code.exists' => 'El código de verificación no es válido. Por favor, solicite un código.',
            'password.required' => 'El campo contraseña es obligatorio.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe incluir al menos una letra mayúscula, una letra minúscula y un número.',
        ];
    }
}
