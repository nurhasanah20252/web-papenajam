<?php

namespace App\Http\Requests\PageBuilder;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $type
 * @property array $content
 * @property array $settings
 * @property int $order
 * @property int|null $parent_id
 */
class StoreBlockRequest extends FormRequest
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
        return [
            'type' => 'required|string|in:text,heading,image,gallery,form,video,html,columns,section,spacer,separator',
            'content' => 'required|array',
            'content.text' => 'nullable|string',
            'content.level' => 'nullable|integer|min:1|max:6',
            'content.url' => 'nullable|string|max:500',
            'content.alt' => 'nullable|string|max:255',
            'content.width' => 'nullable|string',
            'content.height' => 'nullable|string',
            'content.src' => 'nullable|string|max:500',
            'content.caption' => 'nullable|string|max:500',
            'content.html' => 'nullable|string',
            'content.columns' => 'nullable|array',
            'content.columns.*' => 'array',
            'settings' => 'nullable|array',
            'settings.css_class' => 'nullable|string|max:255',
            'settings.css_id' => 'nullable|string|max:255',
            'settings.padding' => 'nullable|string|max:50',
            'settings.margin' => 'nullable|string|max:50',
            'settings.background_color' => 'nullable|string|max:50',
            'settings.text_color' => 'nullable|string|max:50',
            'order' => 'required|integer|min:0',
            'parent_id' => 'nullable|exists:page_blocks,id',
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
            'type.required' => 'The block type is required.',
            'type.in' => 'Invalid block type specified.',
            'content.required' => 'The content field is required.',
            'order.required' => 'The order field is required.',
            'parent_id.exists' => 'The parent block does not exist.',
        ];
    }
}
