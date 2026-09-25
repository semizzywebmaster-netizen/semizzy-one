/* ============================================
   SEMIZZY ONE — Auth Store
   ============================================ */

import type { User, AuthState } from '@/types';

type Listener = (state: AuthState) => void;

class AuthStore {
    private state: AuthState = {
        user: null,
        isAuthenticated: false,
        isLoading: true,
        permissions: [],
        roles: [],
    };

    private listeners: Set<Listener> = new Set();

    getState(): AuthState {
        return { ...this.state };
    }

    setState(partial: Partial<AuthState>) {
        this.state = { ...this.state, ...partial };
        this.notify();
    }

    subscribe(listener: Listener): () => void {
        this.listeners.add(listener);
        return () => this.listeners.delete(listener);
    }

    private notify() {
        this.listeners.forEach((listener) => listener(this.state));
    }

    setUser(user: User | null) {
        this.setState({
            user,
            isAuthenticated: user !== null,
            isLoading: false,
        });
    }

    setPermissions(permissions: string[]) {
        this.setState({ permissions });
    }

    setRoles(roles: string[]) {
        this.setState({ roles });
    }

    hasPermission(permission: string): boolean {
        return this.state.permissions.includes(permission);
    }

    hasRole(role: string): boolean {
        return this.state.roles.includes(role);
    }

    hasAnyPermission(permissions: string[]): boolean {
        return permissions.some((p) => this.state.permissions.includes(p));
    }

    hasAllPermissions(permissions: string[]): boolean {
        return permissions.every((p) => this.state.permissions.includes(p));
    }

    logout() {
        this.setState({
            user: null,
            isAuthenticated: false,
            isLoading: false,
            permissions: [],
            roles: [],
        });
    }
}

export const authStore = new AuthStore();
export default authStore;