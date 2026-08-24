/**
 * app.js — Point d'entrée Vite (référencé dans vite.config.js).
 * Ne contient que le montage React — toute la logique vit dans src/.
 */
import './css/app.css';
import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './src/App.jsx';

const container = document.getElementById('app');
createRoot(container).render(
    <React.StrictMode>
        <App />
    </React.StrictMode>
);
