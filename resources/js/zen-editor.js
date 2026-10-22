/**
 * Zen Editor - Ultra-Smooth Author Studio Typing & Offline Resilience Engine
 * Features:
 * - IndexedDB Client-side caching (No 5MB LocalStorage limit, Instant persistence)
 * - 3-second debounced server autosave with Revision Lock optimistic locking
 * - Zero latency typing & offline state detection
 * - Real-time word count & Zen full-screen mode
 */

class ZenEditorEngine {
    constructor(config) {
        this.chapterId = config.chapterId;
        this.novelId = config.novelId;
        this.initialRevision = config.initialRevision || 1;
        this.currentRevision = this.initialRevision;
        this.clientUuid = this.getOrCreateClientUuid();
        this.autosaveUrl = config.autosaveUrl || '/api/author/chapters/autosave';
        this.csrfToken = config.csrfToken;

        this.titleInput = document.getElementById('chapter-title');
        this.contentArea = document.getElementById('chapter-content');
        this.statusBadge = document.getElementById('sync-status');
        this.wordCounter = document.getElementById('word-count-display');
        this.zenButton = document.getElementById('toggle-zen-mode');

        this.db = null;
        this.debounceTimer = null;
        this.isOnline = navigator.onLine;

        this.init();
    }

    getOrCreateClientUuid() {
        let uuid = sessionStorage.getItem('author_studio_client_uuid');
        if (!uuid) {
            uuid = 'client-' + Math.random().toString(36).substring(2, 15) + '-' + Date.now();
            sessionStorage.setItem('author_studio_client_uuid', uuid);
        }
        return uuid;
    }

    async init() {
        await this.initIndexedDB();
        this.bindEvents();
        this.updateWordCount();
        this.checkLocalDraftRecovery();
    }

    initIndexedDB() {
        return new Promise((resolve, reject) => {
            const request = window.indexedDB.open('Novel3DAuthorStudio', 1);

            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                if (!db.objectStoreNames.contains('chapter_drafts')) {
                    db.createObjectStore('chapter_drafts', { keyPath: 'chapterId' });
                }
            };

            request.onsuccess = (event) => {
                this.db = event.target.result;
                resolve(this.db);
            };

            request.onerror = (event) => {
                console.error('IndexedDB error:', event.target.error);
                resolve(null); // Fallback gracefully
            };
        });
    }

    bindEvents() {
        // Debounce autosave on input
        const triggerInput = () => {
            this.updateWordCount();
            this.setStatus('saving_local', 'Đã lưu trên máy (Offline)');
            this.saveToIndexedDB();

            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.syncToServer();
            }, 3000);
        };

        this.titleInput?.addEventListener('input', triggerInput);
        this.contentArea?.addEventListener('input', triggerInput);

        // Network connectivity detection
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.setStatus('syncing', 'Đã có mạng trở lại, đang đồng bộ...');
            this.syncToServer();
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.setStatus('offline', 'Mất mạng - Đang lưu an toàn trên máy (IndexedDB)');
        });

        // Zen Mode Toggle
        this.zenButton?.addEventListener('click', () => {
            document.body.classList.toggle('zen-mode-active');
            const isZen = document.body.classList.contains('zen-mode-active');
            if (this.zenButton) {
                this.zenButton.innerText = isZen ? 'Thoát Zen Mode' : 'Zen Mode Toàn Màn Hình';
            }
        });
    }

    async saveToIndexedDB() {
        if (!this.db) return;

        const data = {
            chapterId: this.chapterId,
            title: this.titleInput?.value || '',
            content: this.contentArea?.value || '',
            revision: this.currentRevision,
            timestamp: Date.now()
        };

        try {
            const tx = this.db.transaction('chapter_drafts', 'readwrite');
            const store = tx.objectStore('chapter_drafts');
            store.put(data);
        } catch (e) {
            console.warn('Failed to save to IndexedDB', e);
        }
    }

    async checkLocalDraftRecovery() {
        if (!this.db) return;

        try {
            const tx = this.db.transaction('chapter_drafts', 'readonly');
            const store = tx.objectStore('chapter_drafts');
            const req = store.get(this.chapterId);

            req.onsuccess = () => {
                const draft = req.result;
                if (draft && draft.timestamp) {
                    const serverContent = this.contentArea?.value || '';
                    if (draft.content && draft.content.length > serverContent.length && draft.content !== serverContent) {
                        const restorePrompt = confirm('Phát hiện bản thảo offline mới hơn lưu trên trình duyệt của bạn. Bạn có muốn khôi phục không?');
                        if (restorePrompt) {
                            if (this.titleInput) this.titleInput.value = draft.title;
                            if (this.contentArea) this.contentArea.value = draft.content;
                            this.updateWordCount();
                            this.syncToServer();
                        }
                    }
                }
            };
        } catch (e) {
            console.warn('Draft recovery check failed', e);
        }
    }

    async syncToServer() {
        if (!this.isOnline) {
            this.setStatus('offline', 'Đang lưu trên máy (Offline)');
            return;
        }

        const title = this.titleInput?.value || '';
        const content = this.contentArea?.value || '';
        const targetRevision = this.currentRevision + 1;

        this.setStatus('syncing', 'Đang đồng bộ lên máy chủ...');

        try {
            const response = await fetch(this.autosaveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({
                    chapter_id: this.chapterId,
                    title: title,
                    content: content,
                    revision: targetRevision,
                    client_uuid: this.clientUuid
                })
            });

            const result = await response.json();

            if (response.status === 200 && result.status === 'saved') {
                this.currentRevision = result.current_revision;
                this.setStatus('saved', 'Đã đồng bộ lên máy chủ an toàn');
                if (this.wordCounter && result.word_count !== undefined) {
                    this.wordCounter.innerText = result.word_count.toLocaleString() + ' chữ';
                }
            } else if (response.status === 409 && result.status === 'conflict') {
                this.setStatus('conflict', '⚠️ Xung đột: Có phiên bản mới hơn trên server!');
                alert('CẢNH BÁO XUNG ĐỘT:\n' + result.message + '\n\nVui lòng sao chép lại nội dung của bạn trước khi tải lại trang.');
            } else {
                this.setStatus('error', 'Lỗi đồng bộ: ' + (result.message || 'Không thể kết nối'));
            }
        } catch (err) {
            console.warn('Autosave sync failed:', err);
            this.setStatus('offline', 'Đang lưu an toàn trên máy (Mạng chập chờn)');
        }
    }

    updateWordCount() {
        const text = this.contentArea?.value || '';
        const clean = text.replace(/<[^>]*>/g, ' ').trim();
        const count = clean ? clean.split(/\s+/).filter(Boolean).length : 0;
        if (this.wordCounter) {
            this.wordCounter.innerText = count.toLocaleString() + ' chữ';
        }
    }

    setStatus(type, text) {
        if (!this.statusBadge) return;
        this.statusBadge.innerText = text;

        const colors = {
            saved: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            syncing: 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
            saving_local: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            offline: 'bg-amber-500/20 text-amber-300 border-amber-500/40',
            conflict: 'bg-rose-500/20 text-rose-300 border-rose-500/40',
            error: 'bg-rose-500/10 text-rose-400 border-rose-500/20'
        };

        this.statusBadge.className = 'px-3 py-1 rounded-full text-xs font-medium border transition-colors ' + (colors[type] || colors.saved);
    }

    openProjectionModal(paragraphIndex = 0) {
        const modal = document.getElementById('projection3dModal');
        const inputIdx = document.getElementById('modal_paragraph_index');
        if (modal && inputIdx) {
            inputIdx.value = paragraphIndex;
            modal.classList.remove('hidden');
        }
    }

    closeProjectionModal() {
        const modal = document.getElementById('projection3dModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    async saveProjection(data) {
        try {
            const res = await fetch(`/tac-gia/chuong/${this.chapterId}/hieu-ung-3d`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(data)
            });
            if (res.ok) {
                alert('Đã gán mô hình 3D vào đoạn văn thành công!');
                this.closeProjectionModal();
            } else {
                const err = await res.json();
                alert('Lỗi: ' + (err.message || 'Không thể lưu hiệu ứng 3D'));
            }
        } catch (e) {
            alert('Lỗi kết nối máy chủ');
        }
    }
}

window.ZenEditorEngine = ZenEditorEngine;
