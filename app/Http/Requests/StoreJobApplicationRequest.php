<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreJobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string|object>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'department' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:2000'],
            'privacy_accepted' => ['required', 'accepted'],
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }
}
