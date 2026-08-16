import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/composables/useApi';

export const useBudgetStore = defineStore('budgets', () => {
    const budgets = ref([]);
    const loading = ref(false);

    async function fetchAll(params = {}) {
        loading.value = true;
        try {
            const res = await api.get('/budgets', { params });
            budgets.value = res.data;
        } finally {
            loading.value = false;
        }
    }

    async function create(data) {
        const res = await api.post('/budgets', data);
        budgets.value.unshift(res.data);
        return res.data;
    }

    async function update(id, data) {
        const res = await api.put(`/budgets/${id}`, data);
        const idx = budgets.value.findIndex((b) => b.id === id);
        if (idx !== -1) budgets.value[idx] = res.data;
        return res.data;
    }

    async function remove(id) {
        await api.delete(`/budgets/${id}`);
        budgets.value = budgets.value.filter((b) => b.id !== id);
    }

    return { budgets, loading, fetchAll, create, update, remove };
});
