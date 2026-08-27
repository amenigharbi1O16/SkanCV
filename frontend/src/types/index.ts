/**
 * types/index.ts — Types TypeScript partagés dans toute l'app SkanCV.
 * Ces interfaces reflètent les ressources API Laravel (Resources/).
 */

export interface User {
    id: number;
    name: string;
    email: string;
    created_at?: string;
}

export interface JobPosting {
    id: number;
    title: string;
    description: string;
    required_skills: string[];
    cvs_count?: number;
    created_at?: string;
    updated_at?: string;
}

export type AnalysisStatus = 'pending' | 'processing' | 'completed' | 'failed';

export interface Cv {
    id: number;
    job_posting_id: number;
    candidate_name: string;
    candidate_email?: string;
    file_name?: string;
    extracted_skills?: string[];
    analysis_status?: AnalysisStatus;
    similarity_score?: number;
    created_at?: string;
}

export interface Analysis {
    id: number;
    cv_id: number;
    status: AnalysisStatus;
    similarity_score?: number;
    matching_skills?: string[];
    missing_skills?: string[];
    justification?: string;
    analyzed_at?: string;
    candidate_name?: string;
    candidate_email?: string;
    job_posting_id?: number;
    job_posting_title?: string;
}

export interface NotificationData {
    status?: AnalysisStatus;
    message?: string;
    cv_id?: number;
    job_posting_id?: number;
}

export interface Notification {
    id: string;
    type: string;
    data: NotificationData;
    read_at: string | null;
    created_at: string;
}

export interface AuthContextValue {
    token: string | null;
    user: User | null;
    loadingUser: boolean;
    isAuthenticated: boolean;
    login: (email: string, password: string) => Promise<unknown>;
    register: (name: string, email: string, password: string, password_confirmation: string) => Promise<unknown>;
    logout: () => Promise<void>;
    fetchUser: () => Promise<void>;
    updateUser: (data: Partial<User>) => void;
}
