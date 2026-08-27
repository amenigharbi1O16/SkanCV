/**
 * client.ts — Instance Axios centralisée pour toute l'app.
 *
 * MISSION : chaque appel API doit porter le JWT stocké après login, sans
 * que chaque composant ait à le faire manuellement. C'est ici, et
 * seulement ici, que le header Authorization est géré.
 */
import axios from 'axios';

const client = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
    },
});

// Attache le JWT stocké à chaque requête sortante
client.interceptors.request.use((config) => {
    const token = localStorage.getItem('jwt_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Si le token est expiré/invalide (401), on nettoie et redirige vers /login
client.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('jwt_token');
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

export default client;
