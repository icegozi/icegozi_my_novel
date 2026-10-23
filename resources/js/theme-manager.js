/**
 * Theme Manager for Novel Reader Platform
 * Handles light, dark, and sepia modes with localStorage persistence
 */
(function () {
    const THEME_KEY = 'novel_reader_theme';
    const VALID_THEMES = ['light', 'dark', 'sepia'];

    function getPreferredTheme() {
        const saved = localStorage.getItem(THEME_KEY);
        if (saved && VALID_THEMES.includes(saved)) {
            return saved;
        }
        return 'sepia'; // Default platform aesthetic is warm parchment / sepia
    }

    function applyTheme(theme) {
        if (!VALID_THEMES.includes(theme)) return;

        const html = document.documentElement;
        html.classList.remove('light', 'dark', 'sepia');
        html.classList.add(theme);

        // Update meta theme-color
        let metaTheme = document.querySelector('meta[name="theme-color"]');
        if (!metaTheme) {
            metaTheme = document.createElement('meta');
            metaTheme.setAttribute('name', 'theme-color');
            document.head.appendChild(metaTheme);
        }

        const bgMap = {
            light: '#f8fafc',
            dark: '#0b0f19',
            sepia: '#f5efe6'
        };
        metaTheme.setAttribute('content', bgMap[theme]);

        localStorage.setItem(THEME_KEY, theme);
        window.dispatchEvent(new CustomEvent('themechange', { detail: { theme } }));
    }

    window.setReaderTheme = applyTheme;
    window.getReaderTheme = getPreferredTheme;

    window.toggleReaderTheme = function () {
        const current = getPreferredTheme();
        const nextMap = { sepia: 'dark', dark: 'light', light: 'sepia' };
        applyTheme(nextMap[current] || 'sepia');
    };

    // Initialize immediately to prevent flash of wrong theme
    applyTheme(getPreferredTheme());
})();
