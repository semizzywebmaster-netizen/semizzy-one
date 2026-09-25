/* ============================================
   SEMIZZY ONE — Core TypeScript Types
   ============================================ */

// User types
export interface User {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    avatar: string | null;
    email_verified_at: string | null;
    status: 'active' | 'inactive' | 'suspended';
    timezone: string;
    locale: string;
    two_factor_enabled: boolean;
    created_at: string;
    updated_at: string;
}

// Role & Permission types
export interface Role {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    permissions: Permission[];
    created_at: string;
    updated_at: string;
}

export interface Permission {
    id: number;
    name: string;
    slug: string;
    module: string;
    description: string | null;
}

// Auth types
export interface LoginCredentials {
    email: string;
    password: string;
    remember?: boolean;
}

export interface RegisterData {
    name: string;
    email: string;
    phone?: string;
    password: string;
    password_confirmation: string;
}

export interface AuthState {
    user: User | null;
    isAuthenticated: boolean;
    isLoading: boolean;
    permissions: string[];
    roles: string[];
}

// API types
export interface ApiResponse<T = unknown> {
    success: boolean;
    data: T;
    message: string;
    errors?: Record<string, string[]>;
    meta?: PaginationMeta;
}

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

// Navigation types
export interface NavItem {
    label: string;
    href: string;
    icon?: string;
    permission?: string;
    children?: NavItem[];
    badge?: string | number;
}

// Notification types
export interface Notification {
    id: string;
    type: string;
    title: string;
    message: string;
    read: boolean;
    data?: Record<string, unknown>;
    created_at: string;
}

// Settings types
export interface AppSettings {
    app_name: string;
    app_logo: string | null;
    app_favicon: string | null;
    timezone: string;
    locale: string;
    date_format: string;
    maintenance_mode: boolean;
}

// Network/Offline types
export type NetworkStatus = 'ONLINE' | 'OFFLINE' | 'CONNECTING' | 'SYNCING' | 'SYNCED' | 'SYNC_ERROR';

export type SyncStatus = 'PENDING' | 'SYNCING' | 'SYNCED' | 'FAILED' | 'CONFLICT';

export interface OfflineRecord {
    localId: string;
    serverId: string | null;
    operationId: string;
    createdTimestamp: number;
    updatedTimestamp: number;
    syncStatus: SyncStatus;
    retryCount: number;
    conflictStatus: boolean;
}

// System health types
export interface SystemHealth {
    application: HealthCheck;
    php: HealthCheck;
    mysql: HealthCheck;
    storage: HealthCheck;
    cache: HealthCheck;
    queue: HealthCheck;
    cron: HealthCheck;
    pwa: HealthCheck;
    addonEngine: HealthCheck;
}

export interface HealthCheck {
    status: 'healthy' | 'warning' | 'critical';
    message: string;
    details?: Record<string, unknown>;
}

// Addon types
export interface Addon {
    slug: string;
    name: string;
    description: string;
    version: string;
    author: string;
    status: 'installed' | 'active' | 'inactive' | 'error';
    permissions: string[];
    routes: string[];
    settings: Record<string, unknown>;
}

// Provider types
export interface Provider {
    id: number;
    name: string;
    slug: string;
    type: string;
    status: 'active' | 'inactive' | 'error';
    priority: number;
    health: 'healthy' | 'unhealthy';
    last_check: string | null;
}

// Audit log types
export interface AuditLog {
    id: number;
    user_id: number | null;
    user_name: string;
    action: string;
    target_type: string | null;
    target_id: number | null;
    ip_address: string;
    user_agent: string;
    result: 'success' | 'failure';
    metadata: Record<string, unknown>;
    created_at: string;
}

// Page props (Inertia-style for Laravel)
export interface PageProps {
    auth: {
        user: User | null;
        permissions: string[];
        roles: string[];
    };
    settings: AppSettings;
    flash: {
        success?: string;
        error?: string;
        warning?: string;
        info?: string;
    };
    csrf_token: string;
}