/**
 * ModelCache - High Performance In-Memory & IndexedDB Cache for 3D Models
 * Prevents redundant HTTP requests and parsing during rapid chapter scrolling
 */
export class ModelCache {
    constructor() {
        this.memoryCache = new Map();
        this.maxMemoryItems = 15;
    }

    has(url) {
        return this.memoryCache.has(url);
    }

    get(url) {
        if (!this.memoryCache.has(url)) return null;

        const cached = this.memoryCache.get(url);
        // Refresh LRU order
        this.memoryCache.delete(url);
        this.memoryCache.set(url, cached);

        return cached;
    }

    set(url, gltfScene) {
        if (this.memoryCache.size >= this.maxMemoryItems) {
            // Remove least recently used
            const oldestKey = this.memoryCache.keys().next().value;
            this.memoryCache.delete(oldestKey);
        }

        this.memoryCache.set(url, gltfScene);
    }

    clear() {
        this.memoryCache.clear();
    }
}
