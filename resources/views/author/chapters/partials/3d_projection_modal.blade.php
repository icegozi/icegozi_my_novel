<!-- 3D Projection Linker Modal -->
<div id="projection3dModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-6 mx-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-indigo-600/20 text-indigo-400 flex items-center justify-center font-bold text-sm">3D</span>
                <h3 class="text-base font-bold text-white">Gán Mô Hình 3D Cụ Hóa Vào Đoạn Văn</h3>
            </div>
            <button type="button" id="closeProjectionModal" class="text-slate-500 hover:text-white text-lg">&times;</button>
        </div>

        <form id="projection3dForm" class="space-y-4">
            <div>
                <label for="modal_paragraph_index" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">
                    Vị Trí Đoạn Văn (Thứ Tự)
                </label>
                <input type="number" id="modal_paragraph_index" name="paragraph_index" min="0" required
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label for="modal_asset_3d_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">
                    Chọn Mô Hình 3D Trong Kho
                </label>
                <select id="modal_asset_3d_id" name="asset_3d_id" required
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    @if (isset($novel) && $novel->assets3d->isNotEmpty())
                        @foreach ($novel->assets3d as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->name }} ({{ $asset->asset_type }})</option>
                        @endforeach
                    @else
                        <option value="" disabled>Chưa có mô hình nào trong kho</option>
                    @endif
                </select>
            </div>

            <div>
                <label for="modal_hologram_title" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">
                    Tiêu Đề Trình Chiếu Cụ Hóa (Hologram Title) *
                </label>
                <input type="text" id="modal_hologram_title" name="hologram_title" required
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500"
                    placeholder="VD: Cổ Kiếm Thanh Sương Xuất Thế">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="modal_trigger_keyword" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">
                        Từ Khóa Kích Hoạt
                    </label>
                    <input type="text" id="modal_trigger_keyword" name="trigger_keyword"
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500"
                        placeholder="VD: kiếm quang">
                </div>
                <div>
                    <label for="modal_display_mode" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">
                        Chế Độ Hiển Thị
                    </label>
                    <select id="modal_display_mode" name="display_mode"
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="split_stage">Chia Đôi Khung (Split Stage)</option>
                        <option value="inline_box">Hộp Nhúng Nội Tuyến (Inline Box)</option>
                        <option value="hologram_overlay">Lớp Phủ Hologram (Overlay)</option>
                        <option value="background_ambience">Hào Quang Nền (Ambience)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="modal_auto_rotate" name="auto_rotate" value="1" checked
                    class="rounded border-slate-800 bg-slate-950 text-indigo-600 focus:ring-0">
                <label for="modal_auto_rotate" class="text-xs text-slate-300">Tự động xoay mô hình 360 độ</label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="button" id="cancelProjectionBtn" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-500/25 transition">
                    Lưu Hiệu Ứng 3D
                </button>
            </div>
        </form>
    </div>
</div>
