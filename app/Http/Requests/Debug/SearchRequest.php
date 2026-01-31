<?php

namespace App\Http\Requests\Debug;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query' => 'required|string|min:2',
            'pdf_id' => 'nullable|integer|exists:pdfs,id',
        ];
    }
}
