<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLinkRequest extends FormRequest
{
    /**
     * Cualquier visitante puede acortar un enlace.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'original_url' => ['required', 'url:http,https', 'max:2048'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'original_url.required' => 'Escribe la URL que quieres acortar.',
            'original_url.url' => 'La URL debe ser válida y empezar por http:// o https://.',
            'original_url.max' => 'La URL no puede superar los 2048 caracteres.',
            'expires_at.date' => 'La fecha de caducidad no es válida.',
            'expires_at.after' => 'La fecha de caducidad debe ser posterior a hoy.',
        ];
    }
}
