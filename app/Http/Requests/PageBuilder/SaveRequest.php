<?php

namespace App\Http\Requests\PageBuilder;

use Illuminate\Foundation\Http\FormRequest;

class SaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'builder_content' => ['required', 'array'],
            'blocks' => ['sometimes', 'array'],
            'blocks.*.id' => ['sometimes', 'nullable', 'exists:page_blocks,id'],
            'blocks.*.type' => ['required', 'string', \Illuminate\Validation\Rule::enum(\App\Enums\BlockType::class)],
            'blocks.*.content' => ['required', 'array'],
            'blocks.*.settings' => ['sometimes', 'array'],
            'blocks.*.meta' => ['sometimes', 'array'],
            'blocks.*.css_class' => ['sometimes', 'nullable', 'string', 'max:255'],
            'blocks.*.anchor_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'blocks.*.order' => ['required', 'integer', 'min:0'],
            'blocks.*.parent_id' => ['sometimes', 'nullable', 'exists:page_blocks,id'],
        ];
    }
}
