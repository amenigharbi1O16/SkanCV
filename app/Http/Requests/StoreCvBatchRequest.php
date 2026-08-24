<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCvBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'min:1', 'max:20'],
            'files.*' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'candidate_names' => ['sometimes', 'array'],
            'candidate_names.*' => ['nullable', 'string', 'max:255'],
            // Emails optionnels par fichier ; sinon placeholder généré côté controller
            'candidate_emails' => ['sometimes', 'array'],
            'candidate_emails.*' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'files.max' => 'Vous ne pouvez pas uploader plus de 20 CVs en une fois.',
            'files.*.mimes' => 'Chaque fichier doit être un PDF.',
            'files.*.max' => 'Chaque CV ne doit pas dépasser 5 Mo.',
            'candidate_emails.*.email' => 'Chaque email candidat doit être valide.',
        ];
    }
}
