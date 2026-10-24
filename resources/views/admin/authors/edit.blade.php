@extends('layouts.admin')

@section('title', 'Chỉnh sửa tác giả - ' . $author->pen_name)

@section('content')
<div class="space-y-6 max-w-4xl">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.authors.show', $author) }}" 
               class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors"
               aria-label="Quay lại hồ sơ tác giả">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Chỉnh sửa hồ sơ tác giả</h1>
                <p class="text-sm text-slate-400">Cập nhật bút danh và thông tin thuế, thanh toán cho tác giả: <span class="text-indigo-400 font-semibold">{{ $author->pen_name }}</span></p>
            </div>
        </div>
    </div>

    <!-- Edit Form Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm">
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm">
                <p class="font-semibold mb-1">Vui lòng kiểm tra lại thông tin:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.authors.update', $author) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Pen Name -->
                <div class="col-span-2">
                    <label for="pen_name" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Bút danh chính thức <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" 
                           id="pen_name" 
                           name="pen_name" 
                           value="{{ old('pen_name', $author->pen_name) }}" 
                           required 
                           maxlength="100"
                           class="w-full px-4 py-2.5 rounded-lg bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-colors"
                           placeholder="Nhập bút danh hiển thị công khai...">
                </div>

                <!-- Legal Full Name (Readonly) -->
                <div>
                    <label for="legal_full_name" class="block text-sm font-medium text-slate-400 mb-1.5">
                        Họ và tên thật (Theo CCCD - Đã khóa)
                    </label>
                    <input type="text" 
                           id="legal_full_name" 
                           value="{{ $author->legal_full_name }}" 
                           disabled 
                           class="w-full px-4 py-2.5 rounded-lg bg-slate-800/40 border border-slate-800 text-slate-400 cursor-not-allowed">
                </div>

                <!-- Tax Code -->
                <div>
                    <label for="tax_code" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Mã số thuế cá nhân
                    </label>
                    <input type="text" 
                           id="tax_code" 
                           name="tax_code" 
                           value="{{ old('tax_code', $author->tax_code) }}" 
                           maxlength="30"
                           class="w-full px-4 py-2.5 rounded-lg bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-colors"
                           placeholder="Ví dụ: 0312345678...">
                </div>

                <!-- Bank Name -->
                <div>
                    <label for="bank_name" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Tên ngân hàng thụ hưởng
                    </label>
                    <input type="text" 
                           id="bank_name" 
                           name="bank_name" 
                           value="{{ old('bank_name', $author->bank_name) }}" 
                           maxlength="100"
                           class="w-full px-4 py-2.5 rounded-lg bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-colors"
                           placeholder="Ví dụ: Vietcombank, Techcombank, VPBank...">
                </div>

                <!-- Bank Account Number -->
                <div>
                    <label for="bank_account_number" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Số tài khoản ngân hàng
                    </label>
                    <input type="text" 
                           id="bank_account_number" 
                           name="bank_account_number" 
                           value="{{ old('bank_account_number', $author->bank_account_number) }}" 
                           maxlength="50"
                           class="w-full px-4 py-2.5 rounded-lg bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-colors"
                           placeholder="Ví dụ: 999988887777...">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
                <a href="{{ route('admin.authors.show', $author) }}" 
                   class="px-5 py-2.5 rounded-lg border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-800 font-medium text-sm transition-colors">
                    Hủy bỏ
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition-colors shadow-lg shadow-indigo-600/20">
                    Lưu thay đổi hồ sơ
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
