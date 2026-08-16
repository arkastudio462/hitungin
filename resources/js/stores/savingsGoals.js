import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/composables/useApi';

export const useSavingsGoalStore = defineStore('savingsGoals', () => {
    const goals = ref([]);
    const loading = ref(false);

    async function fetchAll() {
        loading.value = true;
        try {
            const res = await api.get('/savings-goals');
            goals.value = res.data;
        } finally {
            loading.value = false;
        }
    }

    async function create(data) {
        const res = await api.post('/savings-goals', data);
        goals.value.unshift(res.data);
        return res.data;
    }

    async function update(id, data) {
        const res = await api.put(`/savings-goals/${id}`, data);
        const idx = goals.value.findIndex((g) => g.id === id);
        if (idx !== -1) goals.value[idx] = res.data;
        return res.data;
    }

    async function remove(id) {
        await api.delete(`/savings-goals/${id}`);
        goals.value = goals.value.filter((g) => g.id !== id);
    }

    async function deposit(id, amount) {
        const res = await api.post(`/savings-goals/${id}/deposit`, { amount });
        const idx = goals.value.findIndex((g) => g.id === id);
        if (idx !== -1) goals.value[idx] = res.data;
        return res.data;
    }

    return { goals, loading, fetchAll, create, update, remove, deposit };
});
