/* ============================================
   SEMIZZY ONE — Offline Page
   ============================================ */

export function OfflinePage() {
    const handleRetry = () => {
        if (navigator.onLine) {
            window.location.reload();
        } else {
            // Show feedback that still offline
            alert('Still offline. Please check your internet connection and try again.');
        }
    };

    return (
        <div className="min-h-screen bg-semizzy-navy flex items-center justify-center p-4">
            <div className="max-w-md w-full text-center">
                {/* Logo */}
                <div className="mb-8">
                    <div className="w-20 h-20 mx-auto bg-semizzy-gradient rounded-2xl flex items-center justify-center mb-4">
                        <span className="text-white text-3xl font-bold">S1</span>
                    </div>
                    <h1 className="text-2xl font-bold text-white">SEMIZZY ONE</h1>
                    <p className="text-semizzy-gray mt-1">Everything You Need. One Platform.</p>
                </div>

                {/* Offline Card */}
                <div className="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/10">
                    <div className="w-16 h-16 mx-auto mb-6 bg-red-500/20 rounded-full flex items-center justify-center">
                        <svg
                            className="w-8 h-8 text-red-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M18.364 5.636a9 9 0 010 12.728m-2.829-2.829a5 5 0 000-7.07m-4.243 2.122a1.5 1.5 0 112.121 2.121 1.5 1.5 0 01-2.121-2.121z"
                            />
                            <line x1="1" y1="1" x2="23" y2="23" strokeWidth={2} />
                        </svg>
                    </div>

                    <h2 className="text-xl font-semibold text-white mb-2">
                        You're Currently Offline
                    </h2>
                    <p className="text-gray-300 mb-2">
                        Your internet connection is unavailable.
                    </p>
                    <p className="text-gray-400 text-sm mb-6">
                        Cached features remain available where supported.
                    </p>

                    {/* Status */}
                    <div className="flex items-center justify-center gap-2 mb-6">
                        <span className="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                        <span className="text-red-400 text-sm font-medium">OFFLINE</span>
                    </div>

                    {/* Retry Button */}
                    <button
                        onClick={handleRetry}
                        className="w-full bg-semizzy-gradient text-white font-semibold py-3 px-6 rounded-xl hover:opacity-90 transition-opacity focus:outline-none focus:ring-2 focus:ring-semizzy-blue focus:ring-offset-2 focus:ring-offset-semizzy-navy"
                    >
                        Try Again
                    </button>
                </div>

                {/* Footer */}
                <p className="text-gray-500 text-xs mt-8">
                    © {new Date().getFullYear()} SEMIZZY WEBMASTER. All rights reserved.
                </p>
            </div>
        </div>
    );
}

export default OfflinePage;