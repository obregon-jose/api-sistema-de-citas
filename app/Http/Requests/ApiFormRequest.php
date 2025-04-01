<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;

class ApiFormRequest extends FormRequest
{

    protected function failedValidation(Validator $validator)
    {
        // throw new HttpResponseException(
        //     response()->json([
        //         'status' => 'error',
        //         'message' => 'Error de validación',
        //         'errors' => $validator->errors()
        //     ], 422)
        // );




    // throw new HttpResponseException(
    //     response()->json([
    //         'status' => 'error',
    //         'message' => $validator->errors()->first()
    //     ], 422)
    // );

    $errors = $validator->errors()->all();
    $message = implode("\n", $errors);
    
    throw new HttpResponseException(
        response()->json([
            'status' => 'error',
            'message' => $message,
            // 'errors' => $errors // Opcional: si quieres mantener el detalle
        ], 422)
    );

    }
}
