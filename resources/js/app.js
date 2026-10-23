import './bootstrap';
import './theme-manager.js';
import './zen-editor.js';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
import { ProjectionStage } from './3d-engine/projection-stage.js';
import { ParticleSystem } from './3d-engine/particle-system.js';
import { ModelCache } from './3d-engine/model-cache.js';

window.Novel3D = {
    ProjectionStage,
    ParticleSystem,
    ModelCache
};

// Global Toast helper
window.showToast = function (message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    const bgColors = {
        success: 'bg-emerald-600 text-white',
        error: 'bg-rose-600 text-white',
        info: 'bg-indigo-600 text-white',
        warning: 'bg-amber-600 text-white'
    };

    toast.className = `px-4 py-3 rounded-xl shadow-xl flex items-center gap-3 text-sm font-medium transition-all duration-300 transform translate-y-2 opacity-0 ${bgColors[type] || bgColors.info}`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'polite');
    toast.innerHTML = `<span>${message}</span>`;

    container.appendChild(toast);
    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
    });

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }, 3500);
};
