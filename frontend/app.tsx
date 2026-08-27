/**
 * app.tsx — Point d'entrée Vite (référencé dans vite.config.ts).
 * Ne contient que le montage React — toute la logique vit dans src/.
 */
import './css/app.css';
import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './src/App';

const container = document.getElementById('app');
if (!container) throw new Error('Root element #app not found');
createRoot(container).render(
    <React.StrictMode>
        <App />
    </React.StrictMode>
);
