<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property array $items
 */
class ReorderMenuItemsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('menus.update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $menuId = $this->route('menu')?->id;

        return [
            'items' => 'required|array|min:1',
            'items.*.id' => "required|exists:menu_items,id,menu_id,{$menuId}",
            'items.*.order' => 'required|integer|min:0',
            'items.*.parent_id' => 'nullable|exists:menu_items,id',
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
            'items.required' => 'At least one menu item must be provided.',
            'items.*.id.required' => 'Each menu item must have an ID.',
            'items.*.id.exists' => 'One or more menu items do not exist or do not belong to this menu.',
            'items.*.order.required' => 'Each menu item must have an order.',
            'items.*.parent_id.exists' => 'One or more parent items do not exist.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items');

            // Check for circular references
            foreach ($items as $item) {
                if (isset($item['parent_id'])) {
                    if ($this->isDescendant($item['id'], $item['parent_id'], $items)) {
                        $validator->errors()->add('items', 'Circular reference detected in menu structure.');
                        break;
                    }
                }
            }
        });
    }

    /**
     * Check if a parent ID is a descendant of the item.
     */
    protected function isDescendant(int $itemId, int $parentId, array $items): bool
    {
        $parentItem = collect($items)->firstWhere('id', $parentId);

        if (! $parentItem) {
            return false;
        }

        if (! isset($parentItem['parent_id'])) {
            return false;
        }

        if ($parentItem['parent_id'] === $itemId) {
            return true;
        }

        return $this->isDescendant($itemId, $parentItem['parent_id'], $items);
    }
}
