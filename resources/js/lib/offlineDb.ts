/* ============================================
   SEMIZZY ONE — IndexedDB Offline Database
   ============================================ */

import type { SyncStatus } from '@/types';

const DB_NAME = 'semizzy-one-offline';
const DB_VERSION = 1;

// Store names
export const STORES = {
    PENDING_OPS: 'pending_operations',
    CACHED_DATA: 'cached_data',
    USER_PREFS: 'user_preferences',
    FORM_DRAFTS: 'form_drafts',
} as const;

export interface PendingOperation {
    id: string;
    operationId: string;
    method: 'POST' | 'PUT' | 'PATCH' | 'DELETE';
    endpoint: string;
    body: string | null;
    headers: Record<string, string>;
    syncStatus: SyncStatus;
    retryCount: number;
    maxRetries: number;
    conflictStatus: boolean;
    localTimestamp: number;
    serverTimestamp: number | null;
    serverId: string | null;
    errorMessage: string | null;
}

export interface CachedData {
    id: string;
    storeName: string;
    data: unknown;
    cachedAt: number;
    expiresAt: number | null;
    etag: string | null;
}

export interface FormDraft {
    id: string;
    formId: string;
    url: string;
    data: Record<string, unknown>;
    savedAt: number;
}

class OfflineDatabase {
    private db: IDBDatabase | null = null;
    private initPromise: Promise<IDBDatabase> | null = null;

    async getDb(): Promise<IDBDatabase> {
        if (this.db) return this.db;
        if (this.initPromise) return this.initPromise;

        this.initPromise = new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onerror = () => reject(request.error);

            request.onupgradeneeded = (event) => {
                const db = (event.target as IDBOpenDBRequest).result;

                // Pending operations store
                if (!db.objectStoreNames.contains(STORES.PENDING_OPS)) {
                    const opsStore = db.createObjectStore(STORES.PENDING_OPS, { keyPath: 'id' });
                    opsStore.createIndex('syncStatus', 'syncStatus', { unique: false });
                    opsStore.createIndex('operationId', 'operationId', { unique: true });
                    opsStore.createIndex('localTimestamp', 'localTimestamp', { unique: false });
                }

                // Cached data store
                if (!db.objectStoreNames.contains(STORES.CACHED_DATA)) {
                    const cacheStore = db.createObjectStore(STORES.CACHED_DATA, { keyPath: 'id' });
                    cacheStore.createIndex('storeName', 'storeName', { unique: false });
                    cacheStore.createIndex('expiresAt', 'expiresAt', { unique: false });
                }

                // User preferences store
                if (!db.objectStoreNames.contains(STORES.USER_PREFS)) {
                    db.createObjectStore(STORES.USER_PREFS, { keyPath: 'id' });
                }

                // Form drafts store
                if (!db.objectStoreNames.contains(STORES.FORM_DRAFTS)) {
                    const draftsStore = db.createObjectStore(STORES.FORM_DRAFTS, { keyPath: 'id' });
                    draftsStore.createIndex('formId', 'formId', { unique: false });
                    draftsStore.createIndex('url', 'url', { unique: false });
                }
            };

            request.onsuccess = () => {
                this.db = request.result;
                resolve(this.db);
            };
        });

        return this.initPromise;
    }

    // ─── Pending Operations ────────────────────────

    async addPendingOperation(op: Omit<PendingOperation, 'id' | 'syncStatus' | 'retryCount' | 'conflictStatus' | 'localTimestamp' | 'serverTimestamp' | 'serverId' | 'errorMessage'>): Promise<string> {
        const db = await this.getDb();
        const id = `op_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

        const record: PendingOperation = {
            ...op,
            id,
            syncStatus: 'PENDING',
            retryCount: 0,
            conflictStatus: false,
            localTimestamp: Date.now(),
            serverTimestamp: null,
            serverId: null,
            errorMessage: null,
        };

        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORES.PENDING_OPS, 'readwrite');
            tx.objectStore(STORES.PENDING_OPS).add(record);
            tx.oncomplete = () => resolve(id);
            tx.onerror = () => reject(tx.error);
        });
    }

    async getPendingOperations(): Promise<PendingOperation[]> {
        const db = await this.getDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORES.PENDING_OPS, 'readonly');
            const request = tx.objectStore(STORES.PENDING_OPS).index('syncStatus').getAll('PENDING');
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async updateOperationStatus(id: string, status: SyncStatus, extra?: Partial<PendingOperation>): Promise<void> {
        const db = await this.getDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORES.PENDING_OPS, 'readwrite');
            const store = tx.objectStore(STORES.PENDING_OPS);
            const getReq = store.get(id);

            getReq.onsuccess = () => {
                const record = getReq.result as PendingOperation;
                if (record) {
                    record.syncStatus = status;
                    if (extra) Object.assign(record, extra);
                    store.put(record);
                }
            };
            tx.oncomplete = () => resolve();
            tx.onerror = () => reject(tx.error);
        });
    }

    async deleteOperation(id: string): Promise<void> {
        const db = await this.getDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORES.PENDING_OPS, 'readwrite');
            tx.objectStore(STORES.PENDING_OPS).delete(id);
            tx.oncomplete = () => resolve();
            tx.onerror = () => reject(tx.error);
        });
    }

    async getOperationByOperationId(operationId: string): Promise<PendingOperation | null> {
        const db = await this.getDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORES.PENDING_OPS, 'readonly');
            const request = tx.objectStore(STORES.PENDING_OPS).index('operationId').get(operationId);
            request.onsuccess = () => resolve(request.result || null);
            request.onerror = () => reject(request.error);
        });
    }

    // ─── Cached Data ───────────────────────────────

    async cacheData(id: string, storeName: string, data: unknown, ttlSeconds?: number): Promise<void> {
        const db = await this.getDb();
        const record: CachedData = {
            id,
            storeName,
            data,
            cachedAt: Date.now(),
            expiresAt: ttlSeconds ? Date.now() + (ttlSeconds * 1000) : null,
            etag: null,
        };

        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORES.CACHED_DATA, 'readwrite');
            tx.objectStore(STORES.CACHED_DATA).put(record);
            tx.oncomplete = () => resolve();
            tx.onerror = () => reject(tx.error);
        });
    }

    async getCachedData(id: string): Promise<CachedData | null> {
        const db = await this.getDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORES.CACHED_DATA, 'readonly');
            const request = tx.objectStore(STORES.CACHED_DATA).get(id);
            request.onsuccess = () => {
                const result = request.result as CachedData | undefined;
                if (!result) return resolve(null);
                // Check expiry
                if (result.expiresAt && result.expiresAt < Date.now()) {
                    this.deleteCachedData(id);
                    return resolve(null);
                }
                resolve(result);
            };
            request.onerror = () => reject(request.error);
        });
    }

    async deleteCachedData(id: string): Promise<void> {
        const db = await this.getDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORES.CACHED_DATA, 'readwrite');
            tx.objectStore(STORES.CACHED_DATA).delete(id);
            tx.oncomplete = () => resolve();
            tx.onerror = () => reject(tx.error);
        });
    }

    async clearExpiredCache(): Promise<number> {
        const db = await this.getDb();
        const now = Date.now();
        let cleared = 0;

        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORES.CACHED_DATA, 'readwrite');
            const store = tx.objectStore(STORES.CACHED_DATA);
            const request = store.openCursor();

            request.onsuccess = () => {
                const cursor = request.result;
                if (cursor) {
                    const record = cursor.value as CachedData;
                    if (record.expiresAt && record.expiresAt < now) {
                        cursor.delete();
                        cleared++;
                    }
                    cursor.continue();
                }
            };

            tx.oncomplete = () => resolve(cleared);
            tx.onerror = () => reject(tx.error);
        });
    }

    // ─── Form Drafts ───────────────────────────────

    async saveDraft(formId: string, url: string, data: Record<string, unknown>): Promise<void> {
        const db = await this.getDb();
        const record: FormDraft = {
            id: `draft_${formId}`,
            formId,
            url,
            data,
            savedAt: Date.now(),
        };

        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORES.FORM_DRAFTS, 'readwrite');
            tx.objectStore(STORES.FORM_DRAFTS).put(record);
            tx.oncomplete = () => resolve();
            tx.onerror = () => reject(tx.error);
        });
    }

    async getDraft(formId: string): Promise<FormDraft | null> {
        const db = await this.getDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORES.FORM_DRAFTS, 'readonly');
            const request = tx.objectStore(STORES.FORM_DRAFTS).get(`draft_${formId}`);
            request.onsuccess = () => resolve(request.result || null);
            request.onerror = () => reject(request.error);
        });
    }

    async deleteDraft(formId: string): Promise<void> {
        const db = await this.getDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORES.FORM_DRAFTS, 'readwrite');
            tx.objectStore(STORES.FORM_DRAFTS).delete(`draft_${formId}`);
            tx.oncomplete = () => resolve();
            tx.onerror = () => reject(tx.error);
        });
    }

    // ─── Utilities ─────────────────────────────────

    async getPendingCount(): Promise<number> {
        const db = await this.getDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORES.PENDING_OPS, 'readonly');
            const request = tx.objectStore(STORES.PENDING_OPS).index('syncStatus').count('PENDING');
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async clearAll(): Promise<void> {
        const db = await this.getDb();
        const storeNames = Object.values(STORES);
        return new Promise((resolve, reject) => {
            const tx = db.transaction(storeNames, 'readwrite');
            for (const name of storeNames) {
                tx.objectStore(name).clear();
            }
            tx.oncomplete = () => resolve();
            tx.onerror = () => reject(tx.error);
        });
    }
}

// Singleton instance
export const offlineDb = new OfflineDatabase();
export default offlineDb;