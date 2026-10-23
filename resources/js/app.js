import './bootstrap';
import './theme-manager.js';
import './zen-editor.js';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
import './reader-controls.js';
import { ProjectionStage } from './3d-engine/projection-stage.js';
import { ParticleSystem } from './3d-engine/particle-system.js';
import { ModelCache } from './3d-engine/model-cache.js';
import { Scroll3dObserver } from './scroll-3d-observer.js';
import { ReaderPreferences } from './reader-preferences.js';
import { ReadingProgressTracker } from './reading-progress-tracker.js';

window.Novel3D = {
    ProjectionStage,
    ParticleSystem,
    ModelCache,
    Scroll3dObserver,
    ReaderPreferences,
    ReadingProgressTracker
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

// Header Search Shortcut: Ctrl+K / Cmd+K
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        const searchInput = document.getElementById('header-search-input') || document.getElementById('search-page-input');
        if (searchInput) {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
    }
});

// Dropdown & Mobile Nav outside-click dismissal
document.addEventListener('click', (e) => {
    const userContainer = document.getElementById('user-menu-container');
    const userDropdown = document.getElementById('user-dropdown');
    if (userContainer && userDropdown && !userContainer.contains(e.target)) {
        userDropdown.classList.add('hidden');
    }

    const mobileToggle = document.getElementById('mobile-menu-toggle');
    const mobileNav = document.getElementById('mobile-nav');
    if (mobileNav && mobileToggle && !mobileNav.contains(e.target) && !mobileToggle.contains(e.target)) {
        mobileNav.classList.add('hidden');
    }
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const userDropdown = document.getElementById('user-dropdown');
        const mobileNav = document.getElementById('mobile-nav');
        if (userDropdown) userDropdown.classList.add('hidden');
        if (mobileNav) mobileNav.classList.add('hidden');
    }
});
