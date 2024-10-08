<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCourseRequest extends FormRequest
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
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'image' => 'required|image|max:2048',
            'description' => 'required|string',
            'cost' => 'required|numeric',
            /*'videos' => 'required|array',
            'videos.*' => [
                'required',
                'file',
                'mimes:mp4,mov,ogg,qt',
                'max:8388608', // Adjust max size if needed
            ],*/
        ];
    }
}
