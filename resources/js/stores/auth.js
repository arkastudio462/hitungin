import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/composables/useApi';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const token = ref(localStorage.getItem('token') || null);

    const isAuthenticated = computed(() => !!token.value);

    async function register(data) {
        const res = await api.post('/register', data);
        user.value = res.data.user;
        token.value = res.data.token;
        localStorage.setItem('token', res.data.token);
        return res.data;
    }

    async function login(data) {
        const res = await api.post('/login', data);
        user.value = res.data.user;
        token.value = res.data.token;
        localStorage.setItem('token', res.data.token);
        return res.data;
    }

    async function logout() {
        try {
            await api.post('/logout');
        } finally {
            user.value = null;
            token.value = null;
            localStorage.removeItem('token');
        }
    }

    async function fetchUser() {
        if (!token.value) return;
        try {
            const res = await api.get('/user');
            user.value = res.data;
        } catch {
            user.value = null;
            token.value = null;
            localStorage.removeItem('token');
        }
    }

    return { user, token, isAuthenticated, register, login, logout, fetchUser };
});
