import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '../composables/useApi';

interface User {
    id: number;
    name: string;
    email: string;
}

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null);
    const token = ref<string | null>(localStorage.getItem('auth_token'));

    const isAuthenticated = computed(() => !!token.value && !!user.value);

    const setAuth = (userData: User, authToken: string) => {
        user.value = userData;
        token.value = authToken;
        localStorage.setItem('auth_token', authToken);
        api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
    };

    const clearAuth = () => {
        user.value = null;
        token.value = null;
        localStorage.removeItem('auth_token');
        delete api.defaults.headers.common['Authorization'];
    };

    const login = async (email: string, password: string) => {
        const response = await api.post('/login', { email, password });
        setAuth(response.data.user, response.data.token);
        return response.data;
    };

    const register = async (name: string, email: string, password: string, passwordConfirmation: string) => {
        const response = await api.post('/register', {
            name,
            email,
            password,
            password_confirmation: passwordConfirmation,
        });
        setAuth(response.data.user, response.data.token);
        return response.data;
    };

    const logout = async () => {
        try {
            await api.post('/logout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            clearAuth();
        }
    };

    const fetchUser = async () => {
        try {
            const response = await api.get('/me');
            user.value = response.data.user;
            return response.data.user;
        } catch (error) {
            clearAuth();
            throw error;
        }
    };

    // Initialize auth if token exists
    if (token.value) {
        fetchUser().catch(() => {
            // Token invalid, clear it
            clearAuth();
        });
    }

    return {
        user,
        token,
        isAuthenticated,
        login,
        register,
        logout,
        fetchUser,
    };
});

