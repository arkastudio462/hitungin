<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useNotificationStore } from '@/stores/notifications';
import { Bell, Check, AlertTriangle, AlertCircle, Info, X } from '@lucide/vue';

const store = useNotificationStore();
const isOpen = ref(false);
const dropdownRef = ref(null);

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});

function handleClickOutside(e) {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        isOpen.value = false;
    }
}

function toggle() {
    isOpen.value = !isOpen.value;
    if (isOpen.value && !store.notifications.length) {
        store.fetchAll();
    }
}

function getNotificationIcon(type) {
    switch (type) {
        case 'budget_critical': return AlertTriangle;
        case 'budget_warning': return AlertCircle;
        default: return Info;
    }
}

function getNotificationColor(type) {
    switch (type) {
        case 'budget_critical': return 'text-danger bg-danger/10';
        case 'budget_warning': return 'text-warning bg-warning/10';
        default: return 'text-primary bg-primary/10';
    }
}

function timeAgo(date) {
    if (!date) return '';
    const d = new Date(date);
    const now = new Date();
    const diff = Math.floor((now - d) / 1000);

    if (diff < 60) return 'Baru saja';
    if (diff < 3600) return `${Math.floor(diff / 60)} menit lalu`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} jam lalu`;
    return `${Math.floor(diff / 86400)} hari lalu`;
}

async function handleMarkRead(id) {
    await store.markRead(id);
}

async function handleMarkAllRead() {
    await store.markAllRead();
}
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <button
            @click="toggle"
            class="relative rounded-full p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800 active:scale-95"
        >
            <Bell class="size-5 text-muted" />
            <span
                v-if="store.hasUnread"
                class="absolute -right-0.5 -top-0.5 flex size-4 items-center justify-center rounded-full bg-danger text-[8px] font-bold text-white"
            >
                {{ store.unreadCount > 9 ? '9+' : store.unreadCount }}
            </span>
        </button>

        <Transition name="dropdown">
            <div
                v-if="isOpen"
                class="absolute right-0 top-full z-50 mt-2 w-80 overflow-hidden rounded-2xl border border-border bg-white shadow-xl dark:bg-gray-800 dark:border-gray-700"
            >
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-border px-4 py-3 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Notifikasi</h3>
                    <button
                        v-if="store.hasUnread"
                        @click="handleMarkAllRead"
                        class="flex items-center gap-1 text-[10px] font-semibold text-primary hover:text-primary-dark active:scale-95"
                    >
                        <Check class="size-3" />
                        Semua dibaca
                    </button>
                </div>

                <!-- List -->
                <div class="max-h-80 overflow-y-auto">
                    <div v-if="store.loading" class="flex items-center justify-center py-8">
                        <div class="size-6 animate-spin rounded-full border-2 border-primary border-t-transparent"></div>
                    </div>

                    <div v-else-if="!store.notifications.length" class="py-8 text-center">
                        <Bell class="mx-auto size-8 text-gray-300" />
                        <p class="mt-2 text-xs text-muted">Belum ada notifikasi</p>
                    </div>

                    <template v-else>
                        <div
                            v-for="n in store.notifications"
                            :key="n.id"
                            class="flex gap-3 border-b border-border/50 px-4 py-3 transition-colors last:border-0 hover:bg-gray-50 dark:hover:bg-gray-700"
                            :class="{ 'bg-primary/5': !n.read_at }"
                        >
                            <span
                                class="flex size-8 shrink-0 items-center justify-center rounded-full"
                                :class="getNotificationColor(n.type)"
                            >
                                <component :is="getNotificationIcon(n.type)" class="size-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-gray-900 dark:text-white">{{ n.title }}</p>
                                <p class="mt-0.5 text-[11px] text-muted line-clamp-2">{{ n.message }}</p>
                                <p class="mt-1 text-[10px] text-muted/70">{{ timeAgo(n.created_at) }}</p>
                            </div>
                            <button
                                v-if="!n.read_at"
                                @click="handleMarkRead(n.id)"
                                class="shrink-0 rounded-full p-1 text-muted/50 transition-colors hover:bg-gray-100 hover:text-primary active:scale-90"
                                title="Tandai dibaca"
                            >
                                <X class="size-3" />
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-8px) scale(0.95);
}
</style>
