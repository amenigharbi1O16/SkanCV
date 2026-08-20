<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCvBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        // auth:api est déjà appliqué au niveau de la route, ici on autorise toujours
        return true;
    }

    public function rules(): array
    {
        return [
            // 'files' est un tableau, chaque entrée doit être un vrai PDF < 5 Mo
            'files' => ['required', 'array', 'min:1', 'max:20'], // limite anti-abus
            'files.*' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            // candidate_names optionnel : sinon on prend le nom du fichier
            'candidate_names' => ['sometimes', 'array'],
            'candidate_names.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'files.max' => 'Vous ne pouvez pas uploader plus de 20 CVs en une fois.',
            'files.*.mimes' => 'Chaque fichier doit être un PDF.',
            'files.*.max' => 'Chaque CV ne doit pas dépasser 5 Mo.',
        ];
    }
}