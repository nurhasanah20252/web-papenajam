<?php

namespace App\Http\Requests\PageBuilder;

use App\Enums\PageStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * @property bool $is_builder_enabled
 * @property array $builder_content
 */
class UpdatePageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $page = $this->route('page');

        return $this->user()->can('update', $page);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $pageId = $this->route('page')?->id;

        return [
            'slug' => 'sometimes|required|string|max:255|unique:pages,slug,'.$pageId,
            'title' => 'sometimes|required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'nullable|array',
            'meta' => 'nullable|array',
            'meta.description' => 'nullable|string|max:500',
            'meta.keywords' => 'nullable|array',
            'meta.keywords.*' => 'string|max:100',
            'featured_image' => 'nullable|string|max:500',
            'status' => ['sometimes', new Enum(PageStatus::class)],
            'template_id' => 'nullable|exists:page_templates,id',
            'published_at' => 'nullable|date',
            'is_builder_enabled' => 'sometimes|boolean',
            'builder_content' => 'nullable|array',
            'builder_content.blocks' => 'nullable|array',
            'builder_content.blocks.*.id' => 'nullable|exists:page_blocks,id',
            'builder_content.blocks.*.type' => 'required|string|in:text,heading,image,gallery,form,video,html,columns,section,spacer,separator',
            'builder_content.blocks.*.content' => 'required|array',
            'builder_content.blocks.*.settings' => 'nullable|array',
            'builder_content.blocks.*.order' => 'required|integer|min:0',
            'builder_content.blocks.*.parent_id' => 'nullable|exists:page_blocks,id',
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
            'slug.unique' => 'This slug is already in use.',
            'template_id.exists' => 'The selected template does not exist.',
            'builder_content.blocks.*.type.in' => 'Invalid block type specified.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'last_edited_by' => $this->user()->id,
        ]);
    }
}
