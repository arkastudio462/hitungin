import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/composables/useApi';

export const useCategoryStore = defineStore('categories', () => {
    const categories = ref([]);
    const loading = ref(false);

    async function fetchAll(type = null) {
        loading.value = true;
        try {
            const params = type ? { type } : {};
            const res = await api.get('/categories', { params });
            categories.value = res.data;
        } finally {
            loading.value = false;
        }
    }

    async function create(data) {
        const res = await api.post('/categories', data);
        categories.value.push(res.data);
        return res.data;
    }

    async function update(id, data) {
        const res = await api.put(`/categories/${id}`, data);
        const idx = categories.value.findIndex((c) => c.id === id);
        if (idx !== -1) categories.value[idx] = res.data;
        return res.data;
    }

    async function remove(id) {
        await api.delete(`/categories/${id}`);
        categories.value = categories.value.filter((c) => c.id !== id);
    }

    return { categories, loading, fetchAll, create, update, remove };
});
