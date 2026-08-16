import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/composables/useApi';

export const useTransactionStore = defineStore('transactions', () => {
    const transactions = ref([]);
    const pagination = ref({});
    const loading = ref(false);

    async function fetchAll(params = {}) {
        loading.value = true;
        try {
            const res = await api.get('/transactions', { params });
            transactions.value = res.data.data;
            pagination.value = {
                current_page: res.data.current_page,
                last_page: res.data.last_page,
                total: res.data.total,
            };
        } finally {
            loading.value = false;
        }
    }

    async function create(data) {
        const res = await api.post('/transactions', data);
        transactions.value.unshift(res.data);
        return res.data;
    }

    async function update(id, data) {
        const res = await api.put(`/transactions/${id}`, data);
        const idx = transactions.value.findIndex((t) => t.id === id);
        if (idx !== -1) transactions.value[idx] = res.data;
        return res.data;
    }

    async function remove(id) {
        await api.delete(`/transactions/${id}`);
        transactions.value = transactions.value.filter((t) => t.id !== id);
    }

    return { transactions, pagination, loading, fetchAll, create, update, remove };
});
