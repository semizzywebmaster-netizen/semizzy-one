/* ============================================
   SEMIZZY ONE — Sync Engine
   ============================================ */

import { offlineDb, type PendingOperation } from './offlineDb';
import type { SyncStatus } from '@/types';

type SyncEventType = 'start' | 'progress' | 'complete' | 'error' | 'conflict';
type SyncListener = (event: { type: SyncEventType; operation?: PendingOperation; error?: string; progress?: number }) => void;

class SyncEngine {
    private isSyncing = false;
    private listeners: Set<SyncListener> = new Set();
    private maxRetries = 3;
    private retryDelayMs = 2000;

    subscribe(listener: SyncListener): () => void {
        this.listeners.add(listener);
        return () => this.listeners.delete(listener);
    }

    private emit(event: Parameters<SyncListener>[0]) {
        this.listeners.forEach((listener) => listener(event));
    }

    async sync(): Promise<{ synced: number; failed: number; conflicts: number }> {
        if (this.isSyncing) {
            return { synced: 0, failed: 0, conflicts: 0 };
        }

        if (!navigator.onLine) {
            return { synced: 0, failed: 0, conflicts: 0 };
        }

        this.isSyncing = true;
        this.emit({ type: 'start' });

        let synced = 0;
        let failed = 0;
        let conflicts = 0;

        try {
            const pending = await offlineDb.getPendingOperations();
            const total = pending.length;

            for (let i = 0; i < pending.length; i++) {
                const op = pending[i];
                this.emit({ type: 'progress', operation: op, progress: Math.round(((i + 1) / total) * 100) });

                try {
                    // Check for duplicate (idempotency)
                    const existing = await offlineDb.getOperationByOperationId(op.operationId);
                    if (existing && existing.syncStatus === 'SYNCED') {
                        await offlineDb.deleteOperation(op.id);
                        continue;
                    }

                    // Mark as syncing
                    await offlineDb.updateOperationStatus(op.id, 'SYNCING');

                    // Execute the operation
                    const response = await fetch(op.endpoint, {
                        method: op.method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-Operation-Id': op.operationId,
                            ...op.headers,
                        },
                        body: op.body,
                        credentials: 'same-origin',
                    });

                    if (response.ok) {
                        const data = await response.json();

                        // Check for conflict
                        if (data.conflict) {
                            await offlineDb.updateOperationStatus(op.id, 'CONFLICT', {
                                conflictStatus: true,
                                serverTimestamp: Date.now(),
                                errorMessage: 'Server returned conflict',
                            });
                            conflicts++;
                            this.emit({ type: 'conflict', operation: op });
                            continue;
                        }

                        // Success — mark synced and remove
                        await offlineDb.updateOperationStatus(op.id, 'SYNCED', {
                            serverId: data.data?.id?.toString() || null,
                            serverTimestamp: Date.now(),
                        });

                        // Clean up synced operation after brief delay
                        setTimeout(() => offlineDb.deleteOperation(op.id), 5000);
                        synced++;
                    } else if (response.status === 409) {
                        // Conflict
                        await offlineDb.updateOperationStatus(op.id, 'CONFLICT', {
                            conflictStatus: true,
                            serverTimestamp: Date.now(),
                            errorMessage: 'Server conflict (409)',
                        });
                        conflicts++;
                        this.emit({ type: 'conflict', operation: op });
                    } else if (response.status >= 500) {
                        // Server error — retry later
                        const newRetryCount = op.retryCount + 1;
                        if (newRetryCount >= this.maxRetries) {
                            await offlineDb.updateOperationStatus(op.id, 'FAILED', {
                                retryCount: newRetryCount,
                                errorMessage: `Server error ${response.status} after ${newRetryCount} retries`,
                            });
                            failed++;
                        } else {
                            await offlineDb.updateOperationStatus(op.id, 'PENDING', {
                                retryCount: newRetryCount,
                                errorMessage: `Server error ${response.status}, retry ${newRetryCount}`,
                            });
                        }
                    } else {
                        // Client error (4xx) — mark as failed, don't retry
                        await offlineDb.updateOperationStatus(op.id, 'FAILED', {
                            errorMessage: `Client error ${response.status}`,
                            serverTimestamp: Date.now(),
                        });
                        failed++;
                    }
                } catch (fetchError) {
                    // Network error during sync — leave as PENDING
                    const newRetryCount = op.retryCount + 1;
                    if (newRetryCount >= this.maxRetries) {
                        await offlineDb.updateOperationStatus(op.id, 'FAILED', {
                            retryCount: newRetryCount,
                            errorMessage: fetchError instanceof Error ? fetchError.message : 'Network error',
                        });
                        failed++;
                    } else {
                        await offlineDb.updateOperationStatus(op.id, 'PENDING', {
                            retryCount: newRetryCount,
                        });
                    }
                }
            }

            this.emit({ type: 'complete' });
        } catch (error) {
            this.emit({ type: 'error', error: error instanceof Error ? error.message : 'Sync failed' });
        } finally {
            this.isSyncing = false;
        }

        // Clean up expired cache
        await offlineDb.clearExpiredCache();

        return { synced, failed, conflicts };
    }

    getIsSyncing(): boolean {
        return this.isSyncing;
    }

    // Queue a safe offline operation (non-financial only)
    async queueOperation(
        method: 'POST' | 'PUT' | 'PATCH' | 'DELETE',
        endpoint: string,
        body?: unknown,
        idempotencyKey?: string
    ): Promise<string> {
        const win = window as unknown as Record<string, unknown>;
        const semizzy = win.__SEMIZZY as Record<string, unknown> | undefined;
        const csrfToken = (semizzy?.csrfToken as string) || '';

        const operationId = idempotencyKey || `op_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

        // Check for duplicate
        const existing = await offlineDb.getOperationByOperationId(operationId);
        if (existing) {
            return existing.id;
        }

        return offlineDb.addPendingOperation({
            operationId,
            method,
            endpoint,
            body: body ? JSON.stringify(body) : null,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Idempotency-Key': operationId,
            },
            maxRetries: this.maxRetries,
        });
    }
}

export const syncEngine = new SyncEngine();
export default syncEngine;