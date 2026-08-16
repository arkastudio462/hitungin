import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/composables/useApi';

export const useTagStore = defineStore('tags', () => {
    const tags = ref([]);
    const loading = ref(false);

    async function fetchAll() {
        loading.value = true;
        try {
            const res = await api.get('/tags');
            tags.value = res.data;
        } finally {
            loading.value = false;
        }
    }

    async function create(data) {
        const res = await api.post('/tags', data);
        tags.value.push(res.data);
        return res.data;
    }

    async function update(id, data) {
        const res = await api.put(`/tags/${id}`, data);
        const idx = tags.value.findIndex((t) => t.id === id);
        if (idx !== -1) tags.value[idx] = res.data;
        return res.data;
    }

    async function remove(id) {
        await api.delete(`/tags/${id}`);
        tags.value = tags.value.filter((t) => t.id !== id);
    }

    return { tags, loading, fetchAll, create, update, remove };
});
