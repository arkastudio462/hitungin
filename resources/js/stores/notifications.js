import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/composables/useApi';

export const useNotificationStore = defineStore('notifications', () => {
    const notifications = ref([]);
    const unreadCount = ref(0);
    const loading = ref(false);

    const hasUnread = computed(() => unreadCount.value > 0);

    async function fetchAll() {
        loading.value = true;
        try {
            const res = await api.get('/notifications');
            notifications.value = res.data.notifications;
            unreadCount.value = res.data.unread_count;
        } finally {
            loading.value = false;
        }
    }

    async function markRead(id) {
        await api.post(`/notifications/${id}/read`);
        const notification = notifications.value.find((n) => n.id === id);
        if (notification && !notification.read_at) {
            notification.read_at = new Date().toISOString();
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        }
    }

    async function markAllRead() {
        await api.post('/notifications/read-all');
        notifications.value.forEach((n) => {
            if (!n.read_at) {
                n.read_at = new Date().toISOString();
            }
        });
        unreadCount.value = 0;
    }

    function addLocalNotification(notification) {
        notifications.value.unshift(notification);
        if (!notification.read_at) {
            unreadCount.value++;
        }
    }

    return { notifications, unreadCount, loading, hasUnread, fetchAll, markRead, markAllRead, addLocalNotification };
});
