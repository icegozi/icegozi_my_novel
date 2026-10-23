<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNovelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'synopsis' => ['required', 'string', 'min:20'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['exists:genres,id'],
            'age_rating' => ['required', 'in:all,13_plus,16_plus,18_plus'],
            'copyright_type' => ['required', 'in:original,authorized_translation,public_domain'],
            'copyright_license_notes' => ['nullable', 'string'],
            'content_warnings' => ['nullable', 'array'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,webp,jpg', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Vui lòng nhập tên tác phẩm.',
            'synopsis.required' => 'Tóm tắt tác phẩm là bắt buộc.',
            'synopsis.min' => 'Tóm tắt truyện phải có độ dài tối thiểu 20 ký tự.',
            'genres.required' => 'Vui lòng chọn ít nhất một thể loại truyện.',
            'age_rating.required' => 'Vui lòng phân loại nhãn độ tuổi phù hợp.',
            'copyright_type.required' => 'Khai báo nguồn gốc bản quyền tác phẩm là bắt buộc.',
        ];
    }
}
