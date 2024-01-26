<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        Log::info('Error al crear el usuario: ' . $validator->errors());
        $response = redirect()->route('users.create')->with('errors', $validator->errors());
        throw new \Illuminate\Validation\ValidationException($validator, $response);   }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'username' => 'required|string|max:255',
            'role' => 'required|string|exists:roles,name',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'nombre',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'username' => 'nombre de usuario',
            'role' => 'rol',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El :attribute es requerido',
            'name.string' => 'El :attribute debe ser una cadena de caracteres',
            'name.max' => 'El :attribute debe tener máximo 255 caracteres',
            'email.required' => 'El :attribute es requerido',
            'email.email' => 'El :attribute debe ser un correo electrónico',
            'email.unique' => 'El :attribute ya existe',
            'password.required' => 'La :attribute es requerida',
            'password.string' => 'La :attribute debe ser una cadena de caracteres',
            'password.min' => 'La :attribute debe tener mínimo 8 caracteres',
            'username.required' => 'El :attribute es requerido',
            'username.string' => 'El :attribute debe ser una cadena de caracteres',
            'username.max' => 'El :attribute debe tener máximo 255 caracteres',
            'role.required' => 'El :attribute es requerido',
            'role.string' => 'El :attribute debe ser una cadena de caracteres',
            'role.exists' => 'El :attribute no existe',
        ];
    }
}
