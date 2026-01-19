<?php

namespace App\Http\Requests\Menu;

use App\Enums\UrlType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * @property string $title
 * @property UrlType $url_type
 * @property string $route_name
 * @property int $page_id
 * @property string $custom_url
 * @property string $icon
 * @property int $order
 * @property bool $target_blank
 * @property bool $is_active
 * @property array $conditions
 * @property int|null $parent_id
 */
class UpdateMenuItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('menuItems.update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $currentItemId = $this->route('item')?->id;

        return [
            'title' => 'sometimes|required|string|max:255',
            'url_type' => ['sometimes', 'required', new Enum(UrlType::class)],
            'route_name' => 'required_if:url_type,route|nullable|string|max:255',
            'page_id' => 'required_if:url_type,page|nullable|exists:pages,id',
            'custom_url' => 'required_if:url_type,custom,external|nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'order' => 'sometimes|required|integer|min:0',
            'target_blank' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'conditions' => 'nullable|array',
            'conditions.*.field' => 'required|string|max:100',
            'conditions.*.operator' => 'required|string|in:=,!=,>,<,>=,<=,contains,not_contains',
            'conditions.*.value' => 'required',
            'display_rules' => 'nullable|array',
            'display_rules.roles' => 'nullable|array',
            'display_rules.roles.*' => ['string', new Enum(\App\Enums\UserRole::class)],
            'display_rules.permissions' => 'nullable|array',
            'display_rules.permissions.*' => 'string|max:100',
            'display_rules.auth' => 'nullable|string|in:guest,logged_in,any',
            'parent_id' => "nullable|exists:menu_items,id|not_in:{$currentItemId}",
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
            'parent_id.not_in' => 'A menu item cannot be its own parent.',
            'conditions.*.operator.in' => 'Invalid condition operator.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $parentId = $this->input('parent_id');
            $currentItemId = $this->route('item')?->id;

            // Prevent circular references
            if ($parentId && $currentItemId) {
                $parentItem = \App\Models\MenuItem::find($parentId);
                if ($parentItem) {
                    // Check if parent belongs to the same menu
                    if ($parentItem->menu_id !== $this->route('menu')->id) {
                        $validator->errors()->add('parent_id', 'Parent menu item must belong to the same menu.');
                    }

                    // Check if parent is a descendant of current item (circular reference)
                    if ($this->isDescendant($currentItemId, $parentId)) {
                        $validator->errors()->add('parent_id', 'Cannot set a child item as parent.');
                    }
                }
            }

            // Validate external URLs
            $urlType = $this->input('url_type');
            if ($urlType === UrlType::External->value && $this->input('custom_url')) {
                if (! filter_var($this->input('custom_url'), FILTER_VALIDATE_URL)) {
                    $validator->errors()->add('custom_url', 'External URL must be a valid URL.');
                }
            }
        });
    }

    /**
     * Check if a potential parent ID is a descendant of the current item.
     */
    protected function isDescendant(int $itemId, int $potentialParentId): bool
    {
        $potentialParent = \App\Models\MenuItem::find($potentialParentId);

        if (! $potentialParent || ! $potentialParent->parent_id) {
            return false;
        }

        if ($potentialParent->parent_id === $itemId) {
            return true;
        }

        return $this->isDescendant($itemId, $potentialParent->parent_id);
    }
}
