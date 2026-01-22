<?php

namespace App\Http\Requests;

use App\Models\FileUpload;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            "user.label" => ['required', 'exists:users,name'],
            "user.value" => ['required', 'exists:users,id'],
            "name" => ['required', 'unique:products,name', 'string', 'max:255'],
            "description" => ['required', 'string', 'max:500'],
            "price" => ['required', 'numeric', 'min:0'],
            "image_url" => ['required', 'url', function($name, $value) {
                return app(FileUpload::class)->isValidFileurl($value);
            }],
        ];
    }

    public function passedValidation()
    {
        $this->merge([
            "user_id" => $this->input("user.value"),
        ]);
    }
}
