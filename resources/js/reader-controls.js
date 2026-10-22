/**
 * Reader Controls Engine
 * Manages typography settings, theme switching, keyboard shortcuts, TOC drawer, and comic view
 */
(function () {
    const STORAGE_KEY = 'reader_display_settings';

    const defaultSettings = {
        fontSize: 18,
        lineHeight: 1.8,
        contentWidth: 'max-w-2xl',
        fontFamily: 'serif',
        theme: 'dark'
    };

    function loadSettings() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            return saved ? { ...defaultSettings, ...JSON.parse(saved) } : defaultSettings;
        } catch (e) {
            return defaultSettings;
        }
    }

    function saveSettings(settings) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(settings));
        } catch (e) {}
    }

    const currentSettings = loadSettings();

    function applySettings() {
        const content = document.getElementById('reading-content-body');
        const container = document.getElementById('reading-container');

        if (content) {
            content.style.fontSize = currentSettings.fontSize + 'px';
            content.style.lineHeight = currentSettings.lineHeight;

            if (currentSettings.fontFamily === 'sans') {
                content.classList.remove('font-novel');
                content.classList.add('font-sans');
            } else {
                content.classList.remove('font-sans');
                content.classList.add('font-novel');
            }
        }

        if (container) {
            container.classList.remove('max-w-xl', 'max-w-2xl', 'max-w-3xl', 'max-w-4xl', 'max-w-full');
            container.classList.add(currentSettings.contentWidth);
        }

        const sizeLabel = document.getElementById('font-size-display-label');
        if (sizeLabel) {
            sizeLabel.textContent = currentSettings.fontSize + 'px';
        }
    }

    window.adjustFontSize = function (delta) {
        currentSettings.fontSize = Math.min(28, Math.max(14, currentSettings.fontSize + delta));
        saveSettings(currentSettings);
        applySettings();
    };

    window.setFontFamily = function (family) {
        currentSettings.fontFamily = family;
        saveSettings(currentSettings);
        applySettings();
    };

    window.setContentWidth = function (widthClass) {
        currentSettings.contentWidth = widthClass;
        saveSettings(currentSettings);
        applySettings();
    };

    window.setLineHeight = function (lh) {
        currentSettings.lineHeight = lh;
        saveSettings(currentSettings);
        applySettings();
    };

    // Keyboard Shortcuts
    document.addEventListener('keydown', function (e) {
        // Ignore if user is typing in an input
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
            return;
        }

        if (e.key === 'ArrowLeft') {
            const prevLink = document.querySelector('a[rel="prev"]');
            if (prevLink) prevLink.click();
        } else if (e.key === 'ArrowRight') {
            const nextLink = document.querySelector('a[rel="next"]');
            if (nextLink) nextLink.click();
        } else if (e.key === 't' || e.key === 'T') {
            if (window.toggleReaderTheme) window.toggleReaderTheme();
        } else if (e.key === 'Escape') {
            const toc = document.getElementById('toc-drawer');
            const settings = document.getElementById('settings-drawer');
            if (toc) toc.classList.add('hidden');
            if (settings) settings.classList.add('hidden');
        }
    });

    // Top Reading Scroll Progress Bar
    window.addEventListener('scroll', function () {
        const progressBar = document.getElementById('reading-progress-bar');
        if (!progressBar) return;

        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        if (docHeight <= 0) {
            progressBar.style.width = '0%';
            return;
        }

        const scrolled = (window.scrollY / docHeight) * 100;
        progressBar.style.width = Math.min(100, Math.max(0, scrolled)) + '%';
    }, { passive: true });

    document.addEventListener('DOMContentLoaded', applySettings);
    // Immediate apply
    applySettings();
})();
