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
class StoreMenuItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('menuItems.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'url_type' => ['required', new Enum(UrlType::class)],
            'route_name' => 'required_if:url_type,route|nullable|string|max:255',
            'page_id' => 'required_if:url_type,page|nullable|exists:pages,id',
            'custom_url' => 'required_if:url_type,custom,external|nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'order' => 'required|integer|min:0',
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
            'parent_id' => 'nullable|exists:menu_items,id',
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
            'title.required' => 'The menu item title is required.',
            'url_type.required' => 'The URL type is required.',
            'route_name.required_if' => 'Route name is required when URL type is route.',
            'page_id.required_if' => 'Page selection is required when URL type is page.',
            'page_id.exists' => 'The selected page does not exist.',
            'custom_url.required_if' => 'Custom URL is required when URL type is custom or external.',
            'parent_id.exists' => 'The parent menu item does not exist.',
            'conditions.*.operator.in' => 'Invalid condition operator.',
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
            'title' => 'menu item title',
            'url_type' => 'URL type',
            'route_name' => 'route name',
            'page_id' => 'page',
            'custom_url' => 'custom URL',
            'parent_id' => 'parent menu item',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->is_active ?? true,
            'target_blank' => $this->target_blank ?? false,
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $urlType = $this->input('url_type');
            $parentId = $this->input('parent_id');

            // Prevent circular references
            if ($parentId) {
                $parentItem = \App\Models\MenuItem::find($parentId);
                if ($parentItem && $parentItem->menu_id !== $this->route('menu')->id) {
                    $validator->errors()->add('parent_id', 'Parent menu item must belong to the same menu.');
                }
            }

            // Validate URL based on type
            if ($urlType === UrlType::External->value && $this->input('custom_url')) {
                if (! filter_var($this->input('custom_url'), FILTER_VALIDATE_URL)) {
                    $validator->errors()->add('custom_url', 'External URL must be a valid URL.');
                }
            }
        });
    }
}
