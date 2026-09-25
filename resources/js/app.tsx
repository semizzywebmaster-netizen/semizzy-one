/* ============================================
   SEMIZZY ONE — Application Entry Point
   ============================================ */

import '../css/app.css';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { NetworkStatusBar } from './components/layout/NetworkStatusBar';

// Get root element
const rootElement = document.getElementById('app');

if (!rootElement) {
    throw new Error('Root element #app not found');
}

// Initialize app
function App() {
    return (
        <StrictMode>
            <NetworkStatusBar />
            {/* App content will be rendered by Laravel blade templates */}
        </StrictMode>
    );
}

// Mount React for interactive components
const root = createRoot(rootElement);
root.render(<App />);

// Register service worker for PWA
if ('serviceWorker' in navigator && import.meta.env.PROD) {
    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register('/sw.js')
            .then((registration) => {
                console.log('SW registered:', registration.scope);
            })
            .catch((error) => {
                console.log('SW registration failed:', error);
            });
    });
}

// Export types for blade template usage
export type { User, PageProps, NetworkStatus } from './types';