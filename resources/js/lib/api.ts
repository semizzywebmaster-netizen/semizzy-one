/* ============================================
   SEMIZZY ONE — API Client
   ============================================ */

import type { ApiResponse } from '@/types';

const API_BASE = '/api/v1';

class ApiClient {
    private csrfToken: string = '';

    setCsrfToken(token: string) {
        this.csrfToken = token;
    }

    private getHeaders(): HeadersInit {
        const headers: HeadersInit = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };

        if (this.csrfToken) {
            headers['X-CSRF-TOKEN'] = this.csrfToken;
        }

        return headers;
    }

    private async request<T>(
        method: string,
        endpoint: string,
        data?: unknown,
        options?: RequestInit
    ): Promise<ApiResponse<T>> {
        const url = endpoint.startsWith('http') ? endpoint : `${API_BASE}${endpoint}`;

        const config: RequestInit = {
            method,
            headers: this.getHeaders(),
            credentials: 'same-origin',
            ...options,
        };

        if (data && method !== 'GET') {
            config.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, config);

            if (response.status === 401) {
                window.location.href = '/login';
                throw new Error('Unauthorized');
            }

            if (response.status === 419) {
                // CSRF token expired - reload page
                window.location.reload();
                throw new Error('CSRF token expired');
            }

            const json = await response.json();

            if (!response.ok) {
                throw {
                    message: json.message || 'An error occurred',
                    errors: json.errors || {},
                    status: response.status,
                };
            }

            return json;
        } catch (error) {
            if (!navigator.onLine) {
                throw {
                    message: 'You are currently offline. This action requires an internet connection.',
                    offline: true,
                };
            }
            throw error;
        }
    }

    async get<T>(endpoint: string, params?: Record<string, string | number | boolean>): Promise<ApiResponse<T>> {
        let url = endpoint;
        if (params) {
            const searchParams = new URLSearchParams();
            Object.entries(params).forEach(([key, value]) => {
                searchParams.set(key, String(value));
            });
            url = `${endpoint}?${searchParams.toString()}`;
        }
        return this.request<T>('GET', url);
    }

    async post<T>(endpoint: string, data?: unknown): Promise<ApiResponse<T>> {
        return this.request<T>('POST', endpoint, data);
    }

    async put<T>(endpoint: string, data?: unknown): Promise<ApiResponse<T>> {
        return this.request<T>('PUT', endpoint, data);
    }

    async patch<T>(endpoint: string, data?: unknown): Promise<ApiResponse<T>> {
        return this.request<T>('PATCH', endpoint, data);
    }

    async delete<T>(endpoint: string): Promise<ApiResponse<T>> {
        return this.request<T>('DELETE', endpoint);
    }
}

export const api = new ApiClient();
export default api;