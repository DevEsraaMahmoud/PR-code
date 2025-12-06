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
            'post_id' => ['nullable', 'integer', 'exists:posts,id'],
            'snippet_id' => ['nullable', 'integer', 'exists:snippets,id'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
            'is_inline' => ['nullable', 'boolean'],
            'start_line' => ['nullable', 'integer', 'min:1', 'required_if:is_inline,true'],
            'end_line' => ['nullable', 'integer', 'min:1', 'required_if:is_inline,true'],
            'body' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            // Either post_id or snippet_id must be provided
            if (empty($data['post_id']) && empty($data['snippet_id'])) {
                $validator->errors()->add('post_id', 'Either post_id or snippet_id is required.');
            }

            // For inline comments, line range is required
            if (!empty($data['is_inline']) && $data['is_inline']) {
                if (empty($data['start_line']) || empty($data['end_line'])) {
                    $validator->errors()->add('start_line', 'Line range is required for inline comments.');
                }

                if (!empty($data['start_line']) && !empty($data['end_line'])) {
                    if ($data['start_line'] > $data['end_line']) {
                        $validator->errors()->add('start_line', 'Start line must be less than or equal to end line.');
                    }
                }
            }
        });
    }
}
