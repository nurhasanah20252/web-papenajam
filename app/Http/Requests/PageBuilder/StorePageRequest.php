<?php

namespace App\Http\Requests\PageBuilder;

use App\Enums\PageStatus;
use App\Enums\PageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * @property bool $is_builder_enabled
 * @property array $builder_content
 * @property int|null $template_id
 */
class StorePageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Page::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'slug' => 'required|string|max:255|unique:pages,slug',
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'nullable|array',
            'meta' => 'nullable|array',
            'meta.description' => 'nullable|string|max:500',
            'meta.keywords' => 'nullable|array',
            'meta.keywords.*' => 'string|max:100',
            'featured_image' => 'nullable|string|max:500',
            'status' => ['sometimes', new Enum(PageStatus::class)],
            'page_type' => ['sometimes', new Enum(PageType::class)],
            'template_id' => 'nullable|exists:page_templates,id',
            'published_at' => 'nullable|date|after_or_equal:now',
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
            'slug.required' => 'The slug field is required.',
            'slug.unique' => 'This slug is already in use.',
            'title.required' => 'The title field is required.',
            'template_id.exists' => 'The selected template does not exist.',
            'published_at.after_or_equal' => 'The published date must be today or in the future.',
            'builder_content.blocks.*.type.in' => 'Invalid block type specified.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'slug' => 'page slug',
            'title' => 'page title',
            'excerpt' => 'excerpt',
            'meta.description' => 'meta description',
            'meta.keywords' => 'meta keywords',
            'featured_image' => 'featured image',
            'template_id' => 'template',
            'builder_content' => 'builder content',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'author_id' => $this->user()->id,
            'status' => $this->status ?? PageStatus::Draft,
            'page_type' => $this->page_type ?? PageType::Page,
        ]);
    }
}
