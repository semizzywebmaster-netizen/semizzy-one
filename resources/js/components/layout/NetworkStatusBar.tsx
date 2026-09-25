/* ============================================
   SEMIZZY ONE — Network Status Bar
   ============================================ */

import { useNetworkStatus } from '@/hooks/useNetworkStatus';

export function NetworkStatusBar() {
    const { status, isOffline, isConnecting, isSyncing, wasOffline } = useNetworkStatus();

    if (status === 'ONLINE' && !wasOffline) {
        return null;
    }

    const statusConfig = {
        OFFLINE: {
            bg: 'bg-red-500',
            text: 'You are offline. Some features may be unavailable.',
            icon: '🔴',
        },
        CONNECTING: {
            bg: 'bg-yellow-500',
            text: 'Reconnecting...',
            icon: '🟡',
        },
        SYNCING: {
            bg: 'bg-blue-500',
            text: 'Syncing data...',
            icon: '🔄',
        },
        SYNCED: {
            bg: 'bg-green-500',
            text: 'Data synced successfully',
            icon: '✅',
        },
        SYNC_ERROR: {
            bg: 'bg-orange-500',
            text: 'Sync error. Will retry automatically.',
            icon: '⚠️',
        },
        ONLINE: {
            bg: 'bg-green-500',
            text: 'Back online',
            icon: '✅',
        },
    };

    const config = statusConfig[status];

    return (
        <div
            className={`fixed top-0 left-0 right-0 z-50 ${config.bg} text-white text-center py-2 px-4 text-sm font-medium transition-all duration-300`}
            role="alert"
            aria-live="polite"
        >
            <span className="mr-2">{config.icon}</span>
            {config.text}
        </div>
    );
}

export default NetworkStatusBar;