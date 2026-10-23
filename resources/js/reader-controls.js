/**
 * Reader Controls Engine
 * Manages typography settings, theme switching, keyboard shortcuts, TOC drawer, comic view, and Zen Mode Toolbar
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

        const percentLabel = document.getElementById('font-size-label');
        if (percentLabel) {
            percentLabel.textContent = Math.round((currentSettings.fontSize / 18) * 100) + '%';
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

    // Zen Toolbar Interactivity
    function initZenToolbar() {
        const zenToggleBtn = document.getElementById('btn-toggle-zen-mode');
        const zenIcon = document.getElementById('zen-icon');
        const zenLabel = document.getElementById('zen-label');

        if (zenToggleBtn) {
            zenToggleBtn.addEventListener('click', function () {
                const isZen = document.body.classList.toggle('zen-mode');
                if (zenIcon) zenIcon.textContent = isZen ? '✕' : '🧘';
                if (zenLabel) zenLabel.textContent = isZen ? 'Thoát Zen Mode' : 'Chế Độ Tinh Gọn';
                localStorage.setItem('reader_zen_mode_active', isZen ? '1' : '0');
            });

            if (localStorage.getItem('reader_zen_mode_active') === '1') {
                document.body.classList.add('zen-mode');
                if (zenIcon) zenIcon.textContent = '✕';
                if (zenLabel) zenLabel.textContent = 'Thoát Zen Mode';
            }
        }

        const btnDecrease = document.getElementById('btn-font-decrease');
        if (btnDecrease) {
            btnDecrease.addEventListener('click', function () {
                window.adjustFontSize(-2);
            });
        }

        const btnIncrease = document.getElementById('btn-font-increase');
        if (btnIncrease) {
            btnIncrease.addEventListener('click', function () {
                window.adjustFontSize(2);
            });
        }

        const themeButtons = document.querySelectorAll('.theme-btn');
        themeButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetTheme = btn.getAttribute('data-theme');
                if (targetTheme && window.setReaderTheme) {
                    window.setReaderTheme(targetTheme);
                    updateActiveThemeButton(targetTheme);
                }
            });
        });

        function updateActiveThemeButton(theme) {
            themeButtons.forEach(function (btn) {
                if (btn.getAttribute('data-theme') === theme) {
                    btn.classList.add('ring-2', 'ring-indigo-500');
                } else {
                    btn.classList.remove('ring-2', 'ring-indigo-500');
                }
            });
        }

        const currentTheme = window.getReaderTheme ? window.getReaderTheme() : (localStorage.getItem('novel_reader_theme') || 'sepia');
        updateActiveThemeButton(currentTheme);

        window.addEventListener('themechange', function (e) {
            if (e.detail && e.detail.theme) {
                updateActiveThemeButton(e.detail.theme);
            }
        });
    }

    // Keyboard Shortcuts
    document.addEventListener('keydown', function (e) {
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

            if (document.body.classList.contains('zen-mode')) {
                document.body.classList.remove('zen-mode');
                const zenIcon = document.getElementById('zen-icon');
                const zenLabel = document.getElementById('zen-label');
                if (zenIcon) zenIcon.textContent = '🧘';
                if (zenLabel) zenLabel.textContent = 'Chế Độ Tinh Gọn';
                localStorage.setItem('reader_zen_mode_active', '0');
            }
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

    document.addEventListener('DOMContentLoaded', function () {
        applySettings();
        initZenToolbar();
    });

    // Immediate attempt if DOM already ready
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
        applySettings();
        initZenToolbar();
    }
})();
