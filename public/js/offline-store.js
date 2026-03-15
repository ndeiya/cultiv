const DB_NAME = 'CultivOfflineStore';
const DB_VERSION = 2; // Increment version for new stores

class OfflineStore {
    constructor() {
        this.db = null;
        this.initPromise = this.init();
        this.isSyncing = false;
    }

    init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onerror = (event) => {
                console.error('IndexedDB error:', event.target.error);
                reject(event.target.error);
            };

            request.onsuccess = (event) => {
                this.db = event.target.result;
                resolve(this.db);
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                
                if (!db.objectStoreNames.contains('attendance_queue')) {
                    db.createObjectStore('attendance_queue', { keyPath: 'id', autoIncrement: true });
                }
                
                if (!db.objectStoreNames.contains('draft_reports')) {
                    db.createObjectStore('draft_reports', { keyPath: 'id', autoIncrement: true });
                }

                // Phase 5.1: New outbox and cache stores
                if (!db.objectStoreNames.contains('outbox')) {
                    db.createObjectStore('outbox', { keyPath: 'id', autoIncrement: true });
                }

                if (!db.objectStoreNames.contains('sync_cache')) {
                    db.createObjectStore('sync_cache', { keyPath: 'url' });
                }
            }
        });
    }

    /**
     * Outbox Management
     */
    async addToOutbox(endpoint, method, data, metadata = {}) {
        await this.initPromise;
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['outbox'], 'readwrite');
            const store = transaction.objectStore('outbox');
            const entry = {
                endpoint,
                method,
                data,
                metadata,
                timestamp: new Date().toISOString(),
                retryCount: 0
            };
            const request = store.add(entry);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async getOutbox() {
        await this.initPromise;
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['outbox'], 'readonly');
            const store = transaction.objectStore('outbox');
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async removeFromOutbox(id) {
        await this.initPromise;
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['outbox'], 'readwrite');
            const store = transaction.objectStore('outbox');
            const request = store.delete(id);
            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Sync Protocol
     */
    async syncOutbox() {
        if (!navigator.onLine || this.isSyncing) return;
        this.isSyncing = true;

        const outbox = await this.getOutbox();
        if (outbox.length === 0) {
            this.isSyncing = false;
            return;
        }

        console.log(`Syncing ${outbox.length} entries from outbox...`);

        try {
            // Group entries for batching if possible, or send sequentially
            // For Phase 5.1, we'll start with a batch endpoint approach or sequential processing
            const response = await fetch('/api/sync/batch', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ entries: outbox })
            });

            const result = await response.json();

            if (result.success) {
                // Clear successfully synced items
                const processedIds = result.synced_ids || [];
                for (const id of processedIds) {
                    await this.removeFromOutbox(id);
                }
                console.log('Sync complete.');
            }
        } catch (error) {
            console.error('Sync failed:', error);
        } finally {
            this.isSyncing = false;
        }
    }

    /**
     * Cache Management
     */
    async setCache(url, data) {
        await this.initPromise;
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['sync_cache'], 'readwrite');
            const store = transaction.objectStore('sync_cache');
            const request = store.put({ url, data, timestamp: new Date().toISOString() });
            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    async getCache(url) {
        await this.initPromise;
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['sync_cache'], 'readonly');
            const store = transaction.objectStore('sync_cache');
            const request = store.get(url);
            request.onsuccess = () => resolve(request.result ? request.result.data : null);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Legacy Compatibility Wrap (Attendance & Reports)
     */
    async saveDraftReport(report) {
        // Automatically route to outbox if it's a submission
        return this.addToOutbox('/api/reports/create', 'POST', report, { type: 'report' });
    }
}

const CultivStore = new OfflineStore();

// Attach global online listener
window.addEventListener('online', () => {
    console.log('App is online. Triggering network sync event...');
    CultivStore.syncOutbox();
    window.dispatchEvent(new CustomEvent('cultiv-sync'));
});

// Periodically attempt sync if items exist
setInterval(() => CultivStore.syncOutbox(), 60000);

// Register service worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
            .then(registration => {
                console.log('ServiceWorker registered with scope:', registration.scope);
            })
            .catch(error => {
                console.log('ServiceWorker registration failed:', error);
            });
    });
}
