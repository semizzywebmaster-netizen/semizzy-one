/* ============================================
   SEMIZZY ONE — Network Status Hook
   ============================================ */

import { useState, useEffect, useCallback } from 'react';
import type { NetworkStatus } from '@/types';

export function useNetworkStatus() {
    const [status, setStatus] = useState<NetworkStatus>(
        navigator.onLine ? 'ONLINE' : 'OFFLINE'
    );
    const [wasOffline, setWasOffline] = useState(false);

    const handleOnline = useCallback(() => {
        setStatus('CONNECTING');
        setWasOffline(true);
        // Brief connecting state, then mark as online
        setTimeout(() => {
            setStatus('ONLINE');
            setTimeout(() => setWasOffline(false), 3000);
        }, 1000);
    }, []);

    const handleOffline = useCallback(() => {
        setStatus('OFFLINE');
    }, []);

    useEffect(() => {
        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);

        return () => {
            window.removeEventListener('online', handleOnline);
            window.removeEventListener('offline', handleOffline);
        };
    }, [handleOnline, handleOffline]);

    const setSyncing = useCallback(() => setStatus('SYNCING'), []);
    const setSynced = useCallback(() => {
        setStatus('SYNCED');
        setTimeout(() => setStatus('ONLINE'), 2000);
    }, []);
    const setSyncError = useCallback(() => {
        setStatus('SYNC_ERROR');
        setTimeout(() => setStatus('ONLINE'), 5000);
    }, []);

    return {
        status,
        isOnline: status === 'ONLINE' || status === 'SYNCED',
        isOffline: status === 'OFFLINE',
        isConnecting: status === 'CONNECTING',
        isSyncing: status === 'SYNCING',
        wasOffline,
        setSyncing,
        setSynced,
        setSyncError,
    };
}