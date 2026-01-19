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
class UpdateMenuRequest extends FormRequest
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
        return [
            'name' => 'sometimes|required|string|max:255',
            'location' => ['sometimes', 'required', new Enum(MenuLocation::class)],
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
            'max_depth.min' => 'Max depth must be at least 1.',
            'max_depth.max' => 'Max depth cannot exceed 5 levels.',
        ];
    }
}
