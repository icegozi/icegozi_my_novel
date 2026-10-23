/**
 * ReadingProgressTracker - Smart 5s Debounced Chapter Reading Tracker & Auto-Resume
 * Accurately syncs paragraph index and scroll percentage to backend & LocalStorage
 */
export class ReadingProgressTracker {
    constructor(chapterId, syncUrl = '/api/reader/progress', csrfToken = '') {
        this.chapterId = chapterId;
        this.syncUrl = syncUrl;
        this.csrfToken = csrfToken;

        this.debounceTimer = null;
        this.currentParagraph = 0;
        this.scrollPercentage = 0;

        this.init();
    }

    init() {
        this.bindScroll();
        this.restoreProgress();
    }

    bindScroll() {
        window.addEventListener('scroll', () => {
            clearTimeout(this.debounceTimer);

            this.debounceTimer = setTimeout(() => {
                this.calculateCurrentPosition();
                this.persistProgress();
            }, 5000); // 5s debounce
        }, { passive: true });
    }

    calculateCurrentPosition() {
        const paragraphs = document.querySelectorAll('[data-paragraph-index]');
        const viewportCenter = window.innerHeight / 2;

        for (const p of paragraphs) {
            const rect = p.getBoundingClientRect();
            if (rect.top <= viewportCenter && rect.bottom >= viewportCenter) {
                this.currentParagraph = parseInt(p.getAttribute('data-paragraph-index'), 10) || 0;
                break;
            }
        }

        const totalScroll = document.documentElement.scrollHeight - window.innerHeight;
        this.scrollPercentage = totalScroll > 0
            ? Math.min(100, Math.max(0, parseFloat(((window.scrollY / totalScroll) * 100).toFixed(2))))
            : 0;
    }

    persistProgress() {
        const payload = {
            chapter_id: this.chapterId,
            paragraph_index: this.currentParagraph,
            scroll_percentage: this.scrollPercentage
        };

        // 1. LocalStorage cache for instant offline restore
        localStorage.setItem(`chapter_progress_${this.chapterId}`, JSON.stringify(payload));

        // 2. Server sync
        if (this.csrfToken) {
            fetch(this.syncUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(payload)
            }).catch(e => console.warn('[ReadingTracker] Sync failed:', e));
        }
    }

    async restoreProgress() {
        // First check LocalStorage
        const local = localStorage.getItem(`chapter_progress_${this.chapterId}`);
        let targetParagraph = 0;

        if (local) {
            try {
                const data = JSON.parse(local);
                targetParagraph = data.paragraph_index || 0;
            } catch (e) {}
        }

        // Check server if available
        if (this.csrfToken) {
            try {
                const res = await fetch(`/api/reader/progress/${this.chapterId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const serverData = await res.json();
                    if (serverData.last_read_paragraph > targetParagraph) {
                        targetParagraph = serverData.last_read_paragraph;
                    }
                }
            } catch (e) {}
        }

        // Auto smooth scroll to target paragraph
        if (targetParagraph > 0) {
            setTimeout(() => {
                const targetElement = document.querySelector(`[data-paragraph-index="${targetParagraph}"]`);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    targetElement.classList.add('paragraph-highlight-3d');
                    setTimeout(() => targetElement.classList.remove('paragraph-highlight-3d'), 3000);
                }
            }, 600);
        }
    }
}

window.ReadingProgressTracker = ReadingProgressTracker;
