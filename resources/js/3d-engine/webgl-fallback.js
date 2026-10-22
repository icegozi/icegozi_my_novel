/**
 * WebGLCapabilityDetector & Graceful 2D Fallback Engine
 * Ensures smooth reader experience on low-end devices, mobile battery saver, or WebGL context crashes
 */
export class WebGLCapabilityDetector {
    /**
     * Check if WebGL context can be successfully initialized
     */
    static isWebGLAvailable() {
        try {
            const canvas = document.createElement('canvas');
            return !!(window.WebGLRenderingContext && 
                (canvas.getContext('webgl') || canvas.getContext('experimental-webgl')));
        } catch (e) {
            return false;
        }
    }

    /**
     * Detect low-end hardware or software rendering
     */
    static isLowEndDevice() {
        if (typeof navigator !== 'undefined') {
            if (navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 2) {
                return true;
            }
            if (navigator.deviceMemory && navigator.deviceMemory <= 2) {
                return true;
            }
        }

        try {
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl');
            if (gl) {
                const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
                if (debugInfo) {
                    const renderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL) || '';
                    if (/swiftshader|llvmpipe|software/i.test(renderer)) {
                        return true;
                    }
                }
            }
        } catch (e) {}

        return false;
    }

    /**
     * Attach WebGL Context Lost and Restored handlers to canvas
     */
    static attachContextLostHandler(canvas, onLostCallback, onRestoredCallback) {
        if (!canvas) return;

        canvas.addEventListener('webglcontextlost', (event) => {
            event.preventDefault();
            console.warn('[WebGLFallback] WebGL Context Lost! Switching to graceful 2D fallback.');
            if (typeof onLostCallback === 'function') {
                onLostCallback(event);
            }
        }, false);

        canvas.addEventListener('webglcontextrestored', (event) => {
            console.info('[WebGLFallback] WebGL Context Restored.');
            if (typeof onRestoredCallback === 'function') {
                onRestoredCallback(event);
            }
        }, false);
    }

    /**
     * Render a clean, elegant 2D fallback card inside the stage container
     */
    static render2dFallback(container, modelName = 'Mô hình 3D', fallbackImageUrl = null, description = '') {
        if (!container) return;

        container.innerHTML = `
            <div class="w-full h-full p-6 flex flex-col items-center justify-center text-center bg-slate-950/90 rounded-2xl border border-slate-800 space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-2xl shadow-inner">
                    ✨
                </div>
                <div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        Chế Độ 2D Tiết Kiệm Pin
                    </span>
                    <h4 class="font-bold text-white text-sm mt-1.5">${modelName}</h4>
                    <p class="text-xs text-slate-400 mt-1 max-w-xs line-clamp-2">${description || 'Thiết bị đang ở chế độ 2D tối ưu hiệu năng và pin.'}</p>
                </div>
            </div>
        `;
    }
}
