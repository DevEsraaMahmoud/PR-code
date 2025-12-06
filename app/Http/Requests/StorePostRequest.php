<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware and policies
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'array'],
            'body.*.type' => ['required', 'string', 'in:text,code'],
            'body.*.content' => ['required', 'string'],
            'body.*.language' => ['required_if:body.*.type,code', 'string', 'nullable'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'visibility' => ['nullable', 'string', 'in:public,private,unlisted'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
