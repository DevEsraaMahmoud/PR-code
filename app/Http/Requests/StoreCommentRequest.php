<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'snippet_id' => ['required', 'integer', 'exists:snippets,id'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
            'start_line' => ['required', 'integer', 'min:1'],
            'end_line' => ['required', 'integer', 'min:1'],
            'body' => ['required', 'string', 'max:5000'],
        ];
    }
}
