/**
 * AuthContext.jsx — État d'authentification partagé dans toute l'app.
 *
 * MISSION : évite le prop-drilling (passer `user`/`login`/`logout` à
 * travers 5 niveaux de composants). N'importe quel composant peut lire
 * `useAuth()` pour savoir si un HR est connecté.
 *
 * RELATION : englobe <App /> dans main.jsx (app.js). Utilisé par
 * pages/Login.jsx pour stocker le JWT après un login réussi, et par
 * router/ProtectedRoute.jsx pour bloquer l'accès si non connecté.
 */
import { createContext, useContext, useState, useCallback } from 'react';
import client from '../api/client';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const [token, setToken] = useState(() => localStorage.getItem('jwt_token'));

    const login = useCallback(async (email, password) => {
        const response = await client.post('/auth/login', { email, password });
        const jwt = response.data.access_token;
        localStorage.setItem('jwt_token', jwt);
        setToken(jwt);
        return response.data;
    }, []);

    const logout = useCallback(async () => {
        try {
            await client.post('/auth/logout');
        } finally {
            localStorage.removeItem('jwt_token');
            setToken(null);
        }
    }, []);

    const value = {
        token,
        isAuthenticated: !!token,
        login,
        logout,
    };

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth doit être utilisé à l\'intérieur de <AuthProvider>');
    }
    return context;
}
