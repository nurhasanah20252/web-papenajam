<?php

namespace App\Http\Requests\PageBuilder;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $name
 * @property string $description
 * @property array $content
 */
class SaveTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('pages.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'content' => 'required|array',
            'content.blocks' => 'required|array',
            'content.blocks.*.type' => 'required|string|in:text,heading,image,gallery,form,video,html,columns,section,spacer,separator',
            'content.blocks.*.content' => 'required|array',
            'content.blocks.*.settings' => 'nullable|array',
            'content.blocks.*.order' => 'required|integer|min:0',
            'content.blocks.*.parent_id' => 'nullable|exists:page_blocks,id',
            'thumbnail' => 'nullable|string|max:500',
            'is_system' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom error messages for validator.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The template name is required.',
            'content.required' => 'The template content is required.',
            'content.blocks.required' => 'The template must have at least one block.',
            'content.blocks.*.type.in' => 'Invalid block type specified.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'created_by' => $this->user()->id,
            'is_system' => $this->is_system ?? false,
        ]);
    }
}
