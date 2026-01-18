<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        $productId = $this->route('product');
        return [
            "user_id" => ['required', 'exists:users,id'],
            "name" => [
                'required', 
                Rule::unique('products', 'name')->ignore($productId, 'id'), 
                'string', 
                'max:255'
            ],
            "description" => ['required', 'string', 'max:500'],
            "price" => ['required', 'numeric', 'min:0'],
            "image_url" => ['required', 'url'],
        ];
    }
}
