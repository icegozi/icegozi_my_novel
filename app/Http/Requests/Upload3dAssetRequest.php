<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Upload3dAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $novel = $this->route('novel');
        if (! $novel) {
            return false;
        }

        return $novel->author_id === auth()->id() || auth()->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'asset_type' => ['required', 'in:character,weapon,beast,artifact,environment'],
            'model_file' => ['required', 'file', 'max:51200'], // max 50MB
            'scale_factor' => ['nullable', 'numeric', 'min:0.1', 'max:10.0'],
            'particle_effect' => ['nullable', 'in:none,fireflies,snow,rain,divine_sparks,sakura'],
            'ambient_light_color' => ['nullable', 'string', 'max:10'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->hasFile('model_file') && $this->file('model_file')->isValid()) {
                $file = $this->file('model_file');
                $handle = fopen($file->getRealPath(), 'rb');
                $header = $handle ? fread($handle, 4) : '';
                if ($handle) {
                    fclose($handle);
                }

                // glTF binary format (.glb) magic header must equal "glTF" (0x46546C67)
                if ($header !== 'glTF') {
                    $validator->errors()->add(
                        'model_file',
                        'Tệp tải lên không phải là mô hình 3D GLB hợp lệ (thiếu binary magic header "glTF").'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên mô hình 3D.',
            'asset_type.required' => 'Vui lòng chọn loại mô hình 3D.',
            'model_file.required' => 'Vui lòng chọn tệp mô hình 3D .glb để tải lên.',
            'model_file.max' => 'Dung lượng tệp mô hình 3D không được vượt quá 50MB.',
        ];
    }
}
