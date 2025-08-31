<?php

declare(strict_types=1);

namespace App\Http\Requests\Kobo;

use Illuminate\Foundation\Http\FormRequest;

final class ImportAccessibleLocationsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only authenticated users can import data
        // TODO: Add proper role-based access control for admin users
        return null !== $this->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'force_update' => ['sometimes', 'boolean'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:1000'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'force_update' => 'forçar atualização',
            'limit' => 'limite de registros',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'limit.min' => 'O limite deve ser pelo menos 1.',
            'limit.max' => 'O limite não pode exceder 1000 registros.',
        ];
    }

    /**
     * Get the validated data from the request.
     *
     * @return array<string, mixed>
     */
    public function validatedWithDefaults(): array
    {
        return array_merge([
            'force_update' => false,
            'limit' => null,
        ], $this->validated());
    }
}
