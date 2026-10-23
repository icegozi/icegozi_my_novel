/**
 * Scroll3dObserver - Fast, Zero-Lag Intersection Observer for 3D Reader Projections
 * Automatically loads 3D models when reading paragraphs come into view
 */
export class Scroll3dObserver {
    constructor(projectionsData, stageInstance, hudElements = {}) {
        this.projections = new Map();
        (projectionsData || []).forEach(p => {
            this.projections.set(parseInt(p.paragraph_index, 10), p);
        });

        this.stage = stageInstance;
        this.hudTitle = hudElements.title;
        this.hudKeyword = hudElements.keyword;
        this.hudBadge = hudElements.badge;

        this.activeParagraphIndex = null;
        this.debounceTimer = null;
        this.observer = null;

        this.init();
    }

    init() {
        if (!('IntersectionObserver' in window)) {
            console.warn('[Scroll3dObserver] IntersectionObserver not supported.');
            return;
        }

        const options = {
            root: null, // viewport
            rootMargin: '-15% 0px -40% 0px', // Active zone in the middle of screen
            threshold: 0.1
        };

        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const pIndex = parseInt(entry.target.getAttribute('data-paragraph-index'), 10);
                    this.onParagraphVisible(pIndex, entry.target);
                }
            });
        }, options);

        // Observe all tagged paragraph elements
        document.querySelectorAll('[data-paragraph-index]').forEach(el => {
            this.observer.observe(el);
        });
    }

    onParagraphVisible(index, element) {
        clearTimeout(this.debounceTimer);

        this.debounceTimer = setTimeout(() => {
            if (this.activeParagraphIndex === index) return;
            this.activeParagraphIndex = index;

            // Clear previous highlights
            document.querySelectorAll('.paragraph-highlight-3d').forEach(el => {
                el.classList.remove('paragraph-highlight-3d');
            });

            if (this.projections.has(index)) {
                const projection = this.projections.get(index);
                element.classList.add('paragraph-highlight-3d');

                // Update HUD
                if (this.hudTitle) this.hudTitle.textContent = projection.hologram_title;
                if (this.hudKeyword && projection.trigger_keyword) {
                    this.hudKeyword.textContent = 'Kích hoạt bởi từ khóa: ' + projection.trigger_keyword;
                }

                // Trigger 3D Stage Load
                if (this.stage && projection.asset3d) {
                    this.stage.loadModel(
                        projection.asset3d.model_file_url,
                        projection.asset3d.scale_factor || 1.0,
                        projection.asset3d.particle_effect || 'none'
                    );
                }
            }
        }, 150); // 150ms debounce for rapid scrolling
    }

    destroy() {
        if (this.observer) {
            this.observer.disconnect();
            this.observer = null;
        }
        clearTimeout(this.debounceTimer);
    }
}

window.Scroll3dObserver = Scroll3dObserver;
