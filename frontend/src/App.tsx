import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import ProtectedRoute from './router/ProtectedRoute';
import AppLayout from './layouts/AppLayout';
import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';
import JobPostingCreate from './pages/JobPostingCreate';
import JobPostingEdit from './pages/JobPostingEdit';
import JobPostingDetail from './pages/JobPostingDetail';
import CvDetail from './pages/CvDetail';
import Profile from './pages/Profile';

export default function App(): JSX.Element {
    return (
        <AuthProvider>
            <BrowserRouter>
                <Routes>
                    <Route path="/login" element={<Login />} />
                    <Route path="/register" element={<Register />} />

                    <Route element={<ProtectedRoute />}>
                        <Route element={<AppLayout />}>
                            <Route path="/" element={<Dashboard />} />
                            <Route path="/profile" element={<Profile />} />
                            <Route path="/job-postings/create" element={<JobPostingCreate />} />
                            <Route path="/job-postings/:id/edit" element={<JobPostingEdit />} />
                            <Route path="/job-postings/:id" element={<JobPostingDetail />} />
                            <Route path="/job-postings/:jobPostingId/cvs/:cvId" element={<CvDetail />} />
                        </Route>
                    </Route>

                    <Route path="*" element={<Navigate to="/" replace />} />
                </Routes>
            </BrowserRouter>
        </AuthProvider>
    );
}
