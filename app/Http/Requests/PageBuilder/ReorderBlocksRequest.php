<?php

namespace App\Http\Requests\PageBuilder;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property array $blocks
 */
class ReorderBlocksRequest extends FormRequest
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
            'blocks' => 'required|array|min:1',
            'blocks.*.id' => 'required|exists:page_blocks,id',
            'blocks.*.order' => 'required|integer|min:0',
            'blocks.*.parent_id' => 'nullable|exists:page_blocks,id',
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
            'blocks.required' => 'At least one block must be provided.',
            'blocks.*.id.required' => 'Each block must have an ID.',
            'blocks.*.id.exists' => 'One or more blocks do not exist.',
            'blocks.*.order.required' => 'Each block must have an order.',
        ];
    }
}
