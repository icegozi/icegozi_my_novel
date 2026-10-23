<!-- Zen Toolbar Component -->
<div id="zen-toolbar" class="fixed bottom-6 right-6 z-40 bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-2xl p-2.5 shadow-2xl flex items-center gap-3">
    <!-- Theme Selector -->
    <div class="flex items-center gap-1.5 bg-slate-950/80 p-1 rounded-xl border border-slate-800">
        <button type="button" class="theme-btn w-7 h-7 rounded-lg bg-slate-950 border border-slate-700 hover:scale-105 active:scale-95 transition touch-manipulation focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" data-theme="dark" title="Giao diện Tối (Dark)" aria-label="Chuyển sang giao diện tối"></button>
        <button type="button" class="theme-btn w-7 h-7 rounded-lg bg-[#f4ecd8] border border-amber-300 hover:scale-105 active:scale-95 transition touch-manipulation focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" data-theme="sepia" title="Giao diện Giấy Cổ (Sepia)" aria-label="Chuyển sang giao diện giấy cổ"></button>
        <button type="button" class="theme-btn w-7 h-7 rounded-lg bg-white border border-slate-200 hover:scale-105 active:scale-95 transition touch-manipulation focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" data-theme="light" title="Giao diện Sáng (Light)" aria-label="Chuyển sang giao diện sáng"></button>
    </div>

    <!-- Font Size Controls -->
    <div class="flex items-center gap-1 bg-slate-950/80 p-1 rounded-xl border border-slate-800 text-xs">
        <button type="button" id="btn-font-decrease" aria-label="A- Giảm kích thước phông chữ" class="px-2.5 py-1 text-slate-300 hover:text-white font-bold transition touch-manipulation rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">A-</button>
        <span id="font-size-label" class="px-1 text-[11px] text-slate-400 font-mono tabular-nums">100%</span>
        <button type="button" id="btn-font-increase" aria-label="A+ Tăng kích thước phông chữ" class="px-2.5 py-1 text-slate-300 hover:text-white font-bold transition touch-manipulation rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">A+</button>
    </div>

    <!-- 1-Click Zen Mode Switcher -->
    <button type="button" id="btn-toggle-zen-mode"
        class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-500/20 flex items-center gap-1.5 transition touch-manipulation focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400">
        <span id="zen-icon" aria-hidden="true">🧘</span>
        <span id="zen-label">Chế Độ Tinh Gọn</span>
    </button>
</div>
