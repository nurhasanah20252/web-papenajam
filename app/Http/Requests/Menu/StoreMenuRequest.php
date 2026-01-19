<?php

namespace App\Http\Requests\Menu;

use App\Enums\MenuLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * @property string $name
 * @property MenuLocation $location
 * @property int $max_depth
 * @property string $description
 */
class StoreMenuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('menus.create');
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
            'location' => ['required', new Enum(MenuLocation::class)],
            'max_depth' => 'nullable|integer|min:1|max:5',
            'description' => 'nullable|string|max:500',
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
            'name.required' => 'The menu name is required.',
            'location.required' => 'The menu location is required.',
            'max_depth.min' => 'Max depth must be at least 1.',
            'max_depth.max' => 'Max depth cannot exceed 5 levels.',
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
            'name' => 'menu name',
            'location' => 'menu location',
            'max_depth' => 'maximum depth',
            'description' => 'description',
        ];
    }
}
