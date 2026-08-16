import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/composables/useApi';

export const useAccountStore = defineStore('accounts', () => {
    const accounts = ref([]);
    const loading = ref(false);

    async function fetchAll() {
        loading.value = true;
        try {
            const res = await api.get('/accounts');
            accounts.value = res.data;
        } finally {
            loading.value = false;
        }
    }

    async function create(data) {
        const res = await api.post('/accounts', data);
        accounts.value.push(res.data);
        return res.data;
    }

    async function update(id, data) {
        const res = await api.put(`/accounts/${id}`, data);
        const idx = accounts.value.findIndex((a) => a.id === id);
        if (idx !== -1) accounts.value[idx] = res.data;
        return res.data;
    }

    async function remove(id) {
        await api.delete(`/accounts/${id}`);
        accounts.value = accounts.value.filter((a) => a.id !== id);
    }

    return { accounts, loading, fetchAll, create, update, remove };
});
