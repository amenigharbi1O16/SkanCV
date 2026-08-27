/**
 * AuthContext.tsx — État d'authentification partagé dans toute l'app.
 *
 * Gère le token JWT, les données de l'utilisateur connecté (HR),
 * ainsi que les actions de login, register, logout et mise à jour de profil.
 */
import { createContext, useContext, useState, useEffect, useCallback, ReactNode } from 'react';
import client from '../api/client';
import type { User, AuthContextValue } from '../types';

const AuthContext = createContext<AuthContextValue | null>(null);

interface AuthProviderProps {
    children: ReactNode;
}

export function AuthProvider({ children }: AuthProviderProps) {
    const [token, setToken] = useState<string | null>(() => localStorage.getItem('jwt_token'));
    const [user, setUser] = useState<User | null>(null);
    const [loadingUser, setLoadingUser] = useState(false);

    const fetchUser = useCallback(async (): Promise<void> => {
        if (!localStorage.getItem('jwt_token')) {
            setUser(null);
            return;
        }
        setLoadingUser(true);
        try {
            const response = await client.get<{ data?: User } | User>('/me');
            const data = response.data as { data?: User } & User;
            setUser(data.data ?? (data as User));
        } catch {
            setUser(null);
        } finally {
            setLoadingUser(false);
        }
    }, []);

    useEffect(() => {
        if (token) {
            fetchUser();
        } else {
            setUser(null);
        }
    }, [token, fetchUser]);

    const login = useCallback(async (email: string, password: string): Promise<unknown> => {
        const response = await client.post<{ token?: string; access_token?: string; user?: User }>('/login', { email, password });
        const jwt = response.data.token ?? response.data.access_token ?? '';
        localStorage.setItem('jwt_token', jwt);
        setToken(jwt);
        if (response.data.user) {
            setUser(response.data.user);
        } else {
            fetchUser();
        }
        return response.data;
    }, [fetchUser]);

    const register = useCallback(async (
        name: string,
        email: string,
        password: string,
        password_confirmation: string
    ): Promise<unknown> => {
        const response = await client.post<{ token?: string; access_token?: string; user?: User }>(
            '/register',
            { name, email, password, password_confirmation }
        );
        const jwt = response.data.token ?? response.data.access_token ?? '';
        localStorage.setItem('jwt_token', jwt);
        setToken(jwt);
        if (response.data.user) {
            setUser(response.data.user);
        } else {
            fetchUser();
        }
        return response.data;
    }, [fetchUser]);

    const logout = useCallback(async (): Promise<void> => {
        try {
            await client.post('/logout');
        } finally {
            localStorage.removeItem('jwt_token');
            setToken(null);
            setUser(null);
        }
    }, []);

    const updateUser = useCallback((updatedData: Partial<User>): void => {
        setUser((prev) => prev ? { ...prev, ...updatedData } : null);
    }, []);

    const value: AuthContextValue = {
        token,
        user,
        loadingUser,
        isAuthenticated: !!token,
        login,
        register,
        logout,
        fetchUser,
        updateUser,
    };

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error("useAuth doit être utilisé à l'intérieur de <AuthProvider>");
    }
    return context;
}
