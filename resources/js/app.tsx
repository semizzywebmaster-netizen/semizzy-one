/* ============================================
   SEMIZZY ONE — Application Entry Point
   ============================================ */

import '../css/app.css';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { NetworkStatusBar } from './components/layout/NetworkStatusBar';

// Mount point.
//
// The Blade layout renders the entire page inside #app and provides a
// dedicated, intentionally empty #network-status island for this React
// component. Mounting into #app is wrong: createRoot().render() discards every
// existing child of the target element and replaces it with what this
// component returns. Since App() renders only <NetworkStatusBar />, which
// returns null while the browser is online, mounting into #app wiped the
// server-rendered page - the login form, the sidebar, all of it - and left a
// blank white page the instant the bundle finished loading.
//
// Absence is not an error either: views such as welcome.blade.php load the
// bundle without providing the island, and throwing here also killed the
// service-worker registration below.
const rootElement = document.getElementById('network-status');

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
if (rootElement) {
    createRoot(rootElement).render(<App />);
}

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