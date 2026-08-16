import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/composables/useApi';

export const useRecurringStore = defineStore('recurring', () => {
    const recurring = ref([]);
    const loading = ref(false);

    async function fetchAll() {
        loading.value = true;
        try {
            const res = await api.get('/recurring-transactions');
            recurring.value = res.data;
        } finally {
            loading.value = false;
        }
    }

    async function create(data) {
        const res = await api.post('/recurring-transactions', data);
        recurring.value.unshift(res.data);
        return res.data;
    }

    async function update(id, data) {
        const res = await api.put(`/recurring-transactions/${id}`, data);
        const idx = recurring.value.findIndex((r) => r.id === id);
        if (idx !== -1) recurring.value[idx] = res.data;
        return res.data;
    }

    async function remove(id) {
        await api.delete(`/recurring-transactions/${id}`);
        recurring.value = recurring.value.filter((r) => r.id !== id);
    }

    return { recurring, loading, fetchAll, create, update, remove };
});
