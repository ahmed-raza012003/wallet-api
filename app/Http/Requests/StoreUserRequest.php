<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // No auth needed
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'initial_balance' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please provide a name for the user.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name cannot exceed 255 characters.',
            'email.required' => 'Please provide an email address.',
            'email.email' => 'The email address must be valid.',
            'email.unique' => 'This email is already taken.',
            'email.max' => 'The email cannot exceed 255 characters.',
            'initial_balance.numeric' => 'The initial balance must be a number.',
            'initial_balance.min' => 'The initial balance cannot be negative.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422));
    }

    protected function prepareForValidation()
{
    $this->merge([
        'name' => trim($this->name),
        'email' => trim(strtolower($this->email)),
    ]);
}
}