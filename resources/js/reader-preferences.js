/**
 * ReaderPreferences - Zen Mode & Reader Customization Manager
 * Supports: Theme switching (dark, sepia, light), Font resizing, Zen Reader Mode
 */
export class ReaderPreferences {
    constructor(config = {}) {
        this.apiEndpoint = config.apiEndpoint || '/api/reader/preferences';
        this.csrfToken = config.csrfToken || '';

        this.fontSizes = ['sm', 'base', 'lg', 'xl'];
        this.fontSizeLabels = { sm: '85%', base: '100%', lg: '115%', xl: '130%' };

        this.preferences = this.loadPreferences();
        this.bindEvents();
        this.apply();
    }

    loadPreferences() {
        const stored = localStorage.getItem('novel_reader_preferences');
        if (stored) {
            try {
                return JSON.parse(stored);
            } catch (e) {}
        }
        return {
            theme: 'dark',
            font_size: 'base',
            mode: '3d'
        };
    }

    savePreferences() {
        localStorage.setItem('novel_reader_preferences', JSON.stringify(this.preferences));

        if (this.csrfToken) {
            fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(this.preferences)
            }).catch(() => {});
        }
    }

    bindEvents() {
        // Theme buttons
        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const theme = e.currentTarget.getAttribute('data-theme');
                if (theme) {
                    this.setTheme(theme);
                }
            });
        });

        // Font size decrease
        document.getElementById('btn-font-decrease')?.addEventListener('click', () => {
            const currentIdx = this.fontSizes.indexOf(this.preferences.font_size);
            if (currentIdx > 0) {
                this.setFontSize(this.fontSizes[currentIdx - 1]);
            }
        });

        // Font size increase
        document.getElementById('btn-font-increase')?.addEventListener('click', () => {
            const currentIdx = this.fontSizes.indexOf(this.preferences.font_size);
            if (currentIdx < this.fontSizes.length - 1) {
                this.setFontSize(this.fontSizes[currentIdx + 1]);
            }
        });

        // Toggle Zen Mode
        document.getElementById('btn-toggle-zen-mode')?.addEventListener('click', () => {
            const newMode = this.preferences.mode === 'zen' ? '3d' : 'zen';
            this.setMode(newMode);
        });
    }

    setTheme(theme) {
        this.preferences.theme = theme;
        this.applyTheme();
        this.savePreferences();
    }

    setFontSize(size) {
        this.preferences.font_size = size;
        this.applyFontSize();
        this.savePreferences();
    }

    setMode(mode) {
        this.preferences.mode = mode;
        this.applyMode();
        this.savePreferences();
    }

    apply() {
        this.applyTheme();
        this.applyFontSize();
        this.applyMode();
    }

    applyTheme() {
        document.body.classList.remove('theme-dark', 'theme-sepia', 'theme-light');
        document.body.classList.add('theme-' + (this.preferences.theme || 'dark'));
    }

    applyFontSize() {
        const body = document.getElementById('reading-content-body');
        const label = document.getElementById('font-size-label');
        if (body) {
            this.fontSizes.forEach(s => body.classList.remove('size-' + s));
            body.classList.add('size-' + (this.preferences.font_size || 'base'));
        }
        if (label) {
            label.textContent = this.fontSizeLabels[this.preferences.font_size] || '100%';
        }
    }

    applyMode() {
        const stageAside = document.querySelector('aside');
        const article = document.querySelector('article');
        const zenLabel = document.getElementById('zen-label');
        const zenIcon = document.getElementById('zen-icon');

        if (this.preferences.mode === 'zen') {
            document.body.classList.add('zen-mode');
            stageAside?.classList.add('hidden');
            if (article) {
                article.classList.remove('lg:col-span-7');
                article.classList.add('lg:col-span-12', 'max-w-3xl', 'mx-auto');
            }
            if (zenLabel) zenLabel.textContent = 'Bật Đài 3D';
            if (zenIcon) zenIcon.textContent = '✨';
        } else {
            document.body.classList.remove('zen-mode');
            stageAside?.classList.remove('hidden');
            if (article) {
                article.classList.remove('lg:col-span-12', 'max-w-3xl', 'mx-auto');
                article.classList.add('lg:col-span-7');
            }
            if (zenLabel) zenLabel.textContent = 'Chế Độ Tinh Gọn';
            if (zenIcon) zenIcon.textContent = '🧘';
        }
    }
}

window.ReaderPreferences = ReaderPreferences;
