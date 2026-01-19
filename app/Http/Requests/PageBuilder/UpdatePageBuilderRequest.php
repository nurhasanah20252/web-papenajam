<?php

namespace App\Http\Requests\PageBuilder;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageBuilderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'builder_content' => ['required', 'array'],
            'html_content' => ['nullable', 'string'],
        ];
    }
}
