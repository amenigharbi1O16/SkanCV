/**
 * app.tsx — Point d'entrée Vite (référencé dans vite.config.ts).
 */
import './css/app.css';
import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './src/App';

// Catch all unhandled errors and show them in the DOM
window.addEventListener('error', (e) => {
    const div = document.getElementById('app');
    if (div) {
        div.innerHTML = `<div style="font-family:monospace;padding:20px;color:red;background:#fff">
            <h2>JavaScript Error</h2>
            <pre style="white-space:pre-wrap">${e.message}\n${e.filename}:${e.lineno}\n${e.error?.stack || ''}</pre>
        </div>`;
    }
});

window.addEventListener('unhandledrejection', (e) => {
    const div = document.getElementById('app');
    if (div) {
        div.innerHTML = `<div style="font-family:monospace;padding:20px;color:red;background:#fff">
            <h2>Unhandled Promise Rejection</h2>
            <pre style="white-space:pre-wrap">${e.reason?.stack || String(e.reason)}</pre>
        </div>`;
    }
});

const container = document.getElementById('app');
if (!container) throw new Error('Root element #app not found');

try {
    createRoot(container).render(
        <React.StrictMode>
            <App />
        </React.StrictMode>
    );
} catch (err: unknown) {
    const e = err as Error;
    container.innerHTML = `<div style="font-family:monospace;padding:20px;color:red;background:#fff">
        <h2>React Render Error</h2>
        <pre style="white-space:pre-wrap">${e?.stack || String(e)}</pre>
    </div>`;
}
