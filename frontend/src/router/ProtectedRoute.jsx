/**
 * ProtectedRoute.jsx — Garde d'accès pour les routes réservées aux HR connectés.
 *
 * MISSION : redirige vers /login si isAuthenticated est false, sinon
 * rend la route demandée. Ne contient aucune logique métier propre.
 */
import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function ProtectedRoute() {
    const { isAuthenticated } = useAuth();
    return isAuthenticated ? <Outlet /> : <Navigate to="/login" replace />;
}
