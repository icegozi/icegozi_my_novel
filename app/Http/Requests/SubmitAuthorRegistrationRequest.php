<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAuthorRegistrationRequest extends FormRequest
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
            'pen_name' => ['required', 'string', 'max:100'],
            'legal_full_name' => ['required', 'string', 'max:150'],
            'identity_number' => ['required', 'string', 'regex:/^[0-9]{12}$/'],
            'phone' => ['required', 'string', 'regex:/^(03|05|07|08|09)[0-9]{8}$/'],
            'identity_issued_date' => ['nullable', 'date', 'before_or_equal:today'],
            'identity_front_image' => ['required', 'image', 'mimes:jpeg,png,webp,jpg', 'max:5120'],
            'identity_back_image' => ['required', 'image', 'mimes:jpeg,png,webp,jpg', 'max:5120'],
            'tax_code' => ['nullable', 'string', 'max:30'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'agree_copyright' => ['accepted'],
            'agree_terms' => ['accepted'],
        ];
    }

    /**
     * Custom validation messages in Vietnamese for clear legal transparency.
     */
    public function messages(): array
    {
        return [
            'pen_name.required' => 'Vui lòng nhập bút danh hiển thị của bạn.',
            'legal_full_name.required' => 'Họ và tên thật trên CCCD/Hộ chiếu là bắt buộc theo quy định pháp luật.',
            'identity_number.required' => 'Số định danh cá nhân (CCCD 12 số) là bắt buộc.',
            'identity_number.regex' => 'Số CCCD phải gồm đúng 12 chữ số hợp lệ.',
            'phone.required' => 'Số điện thoại liên lạc là bắt buộc theo Nghị định 147/2024/NĐ-CP.',
            'phone.regex' => 'Số điện thoại phải là đầu số di động hợp lệ tại Việt Nam (10 chữ số, bắt đầu bằng 03, 05, 07, 08, 09).',
            'identity_front_image.required' => 'Vui lòng tải lên ảnh mặt trước CCCD.',
            'identity_back_image.required' => 'Vui lòng tải lên ảnh mặt sau CCCD.',
            'agree_copyright.accepted' => 'Bạn phải đọc và ký cam kết bản quyền số theo Nghị định 17/2023/NĐ-CP.',
            'agree_terms.accepted' => 'Bạn phải đồng ý với Điều khoản dịch vụ và Chính sách bảo vệ dữ liệu cá nhân (NĐ 13/2023/NĐ-CP).',
        ];
    }
}
