<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useThemeStore } from '@/stores/theme';
import { useNotificationStore } from '@/stores/notifications';
import api from '@/composables/useApi';
import NotificationDropdown from '@/components/NotificationDropdown.vue';
import ReceiptScanner from '@/components/ReceiptScanner.vue';
import {
    LayoutDashboard,
    ArrowLeftRight,
    Tags,
    PiggyBank,
    BarChart3,
    LogOut,
    User,
    Sun,
    Moon,
    CreditCard,
    Repeat,
    Target,
    Camera,
    MoreHorizontal,
    Smartphone,
    X,
} from '@lucide/vue';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const theme = useThemeStore();
const notificationStore = useNotificationStore();

const showProfileMenu = ref(false);
const profileMenuRef = ref(null);
const showMoreMenu = ref(false);
const showScanner = ref(false);

const activeRoute = computed(() => route.name);

const userInitials = computed(() => {
    if (!auth.user?.name) return '?';
    return auth.user.name
        .split(' ')
        .map((w) => w[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
});

const moreMenuItems = [
    { name: 'accounts', label: 'Akun', icon: CreditCard, path: '/accounts' },
    { name: 'categories', label: 'Kategori', icon: Tags, path: '/categories' },
    { name: 'reports', label: 'Laporan', icon: BarChart3, path: '/reports' },
    { name: 'recurring', label: 'Berulang', icon: Repeat, path: '/recurring' },
    { name: 'savings-goals', label: 'Tabungan', icon: Target, path: '/savings-goals' },
    { name: 'notification-forwards', label: 'Notifikasi', icon: Smartphone, path: '/notification-forwards' },
    { name: 'profile', label: 'Profil', icon: User, path: '/profile' },
];

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    checkAppVersion();
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});

function handleClickOutside(e) {
    if (profileMenuRef.value && !profileMenuRef.value.contains(e.target)) {
        showProfileMenu.value = false;
    }
}

function toggleProfileMenu() {
    showProfileMenu.value = !showProfileMenu.value;
}

function goToProfile() {
    showProfileMenu.value = false;
    router.push('/profile');
}

async function handleLogout() {
    showProfileMenu.value = false;
    showMoreMenu.value = false;
    await auth.logout();
    router.push('/login');
}

function goTo(path) {
    showMoreMenu.value = false;
    router.push(path);
}

function onScanSaved() {
    showScanner.value = false;
}

const APP_VERSION_KEY = 'hitungin_app_version';

async function checkAppVersion() {
    try {
        const storedVersion = localStorage.getItem(APP_VERSION_KEY) || '0.0.0';
        const res = await api.get('/app/version', { params: { current_version: storedVersion } });
        if (res.data.update_available) {
            notificationStore.fetchAll();
        }
        localStorage.setItem(APP_VERSION_KEY, res.data.latest_version);
    } catch {
        // silently ignore version check failures
    }
}
</script>

<template>
    <div class="min-h-screen overflow-x-hidden bg-surface pb-24 dark:bg-gray-900 transition-colors duration-300">
        <!-- Header -->
        <header class="sticky top-0 z-40 border-b border-border bg-white/85 backdrop-blur-sm dark:bg-gray-900/85 dark:border-gray-800">
            <div class="pt-[env(safe-area-inset-top)]">
                <div class="mx-auto flex h-14 max-w-lg items-center justify-between px-4">
                <div class="flex items-center gap-2">
                    <div class="flex size-7 items-center justify-center rounded-lg bg-primary text-sm font-extrabold text-white">H</div>
                    <span class="text-lg font-extrabold text-gray-900 dark:text-white">Hitungin</span>
                </div>
                <div class="flex items-center gap-1">
                    <!-- Theme Toggle -->
                    <button
                        @click="theme.toggle()"
                        class="flex size-8 items-center justify-center rounded-full transition-colors hover:bg-gray-100 dark:hover:bg-gray-800 active:scale-95"
                    >
                        <Moon v-if="!theme.isDark" class="size-4.5 text-muted" />
                        <Sun v-else class="size-4.5 text-yellow-400" />
                    </button>

                    <!-- Notification Bell -->
                    <NotificationDropdown />

                    <!-- Profile Dropdown -->
                    <div ref="profileMenuRef" class="relative">
                        <button
                            @click="toggleProfileMenu"
                            class="flex items-center gap-1.5 rounded-full py-1.5 pl-1.5 pr-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800 active:scale-95"
                        >
                            <span
                                class="flex size-7 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-white"
                            >
                                {{ userInitials }}
                            </span>
                        </button>

                        <Transition name="dropdown">
                            <div
                                v-if="showProfileMenu"
                                class="absolute right-0 top-full z-50 mt-2 w-56 overflow-hidden rounded-2xl border border-border bg-white shadow-xl dark:bg-gray-800 dark:border-gray-700"
                            >
                                <!-- User Info -->
                                <div class="border-b border-border px-4 py-3 dark:border-gray-700">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ auth.user?.name || 'User' }}</p>
                                    <p class="mt-0.5 text-[11px] text-muted">{{ auth.user?.email || '' }}</p>
                                </div>

                                <!-- Menu Items -->
                                <div class="py-1">
                                    <button
                                        @click="goToProfile"
                                        class="flex w-full items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    >
                                        <User class="size-4 text-muted" />
                                        Edit Profil
                                    </button>
                                    <button
                                        @click="handleLogout"
                                        class="flex w-full items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-danger transition-colors hover:bg-red-50 dark:hover:bg-red-900/20"
                                    >
                                        <LogOut class="size-4" />
                                        Keluar
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-lg px-4 py-4">
            <RouterView />
        </main>

        <!-- Bottom Tab Bar -->
        <nav class="fixed inset-x-0 bottom-0 z-50 px-4 pb-[calc(1rem+env(safe-area-inset-bottom))]">
            <div class="mx-auto flex max-w-lg items-center justify-around rounded-2xl border border-border bg-white p-2 shadow-lg dark:bg-gray-800 dark:border-gray-700">
                <!-- Beranda -->
                <RouterLink
                    to="/"
                    class="flex flex-1 flex-col items-center gap-0.5 rounded-xl py-1.5 transition-all duration-200"
                    :class="activeRoute === 'dashboard' ? 'text-primary' : 'text-muted active:scale-95'"
                >
                    <LayoutDashboard class="size-5 transition-transform duration-200" :class="activeRoute === 'dashboard' ? 'scale-110' : ''" />
                    <span class="text-[10px] font-semibold">Beranda</span>
                </RouterLink>

                <!-- Transaksi -->
                <RouterLink
                    to="/transactions"
                    class="flex flex-1 flex-col items-center gap-0.5 rounded-xl py-1.5 transition-all duration-200"
                    :class="activeRoute === 'transactions' ? 'text-primary' : 'text-muted active:scale-95'"
                >
                    <ArrowLeftRight class="size-5 transition-transform duration-200" :class="activeRoute === 'transactions' ? 'scale-110' : ''" />
                    <span class="text-[10px] font-semibold">Transaksi</span>
                </RouterLink>

                <!-- Scan -->
                <button
                    @click="showScanner = true"
                    class="flex flex-1 flex-col items-center gap-0.5 rounded-xl py-1.5 transition-all duration-200 text-muted active:scale-95"
                >
                    <div class="flex size-10 items-center justify-center rounded-full bg-primary text-white shadow-lg shadow-primary/30 -mt-4">
                        <Camera class="size-5" />
                    </div>
                    <span class="text-[10px] font-semibold">Scan</span>
                </button>

                <!-- Anggaran -->
                <RouterLink
                    to="/budgets"
                    class="flex flex-1 flex-col items-center gap-0.5 rounded-xl py-1.5 transition-all duration-200"
                    :class="activeRoute === 'budgets' ? 'text-primary' : 'text-muted active:scale-95'"
                >
                    <PiggyBank class="size-5 transition-transform duration-200" :class="activeRoute === 'budgets' ? 'scale-110' : ''" />
                    <span class="text-[10px] font-semibold">Anggaran</span>
                </RouterLink>

                <!-- Lainnya -->
                <button
                    @click="showMoreMenu = true"
                    class="flex flex-1 flex-col items-center gap-0.5 rounded-xl py-1.5 transition-all duration-200 text-muted active:scale-95"
                >
                    <MoreHorizontal class="size-5" />
                    <span class="text-[10px] font-semibold">Lainnya</span>
                </button>
            </div>
        </nav>

        <!-- Lainnya Modal (slide-up like form modals) -->
        <Teleport to="body">
            <Transition name="fade">
                <div
                    v-if="showMoreMenu"
                    class="fixed inset-0 z-[100] bg-black/40"
                    @click="showMoreMenu = false"
                ></div>
            </Transition>
            <Transition name="slide-up">
                <div
                    v-if="showMoreMenu"
                    class="fixed inset-x-0 bottom-0 z-[101] mx-auto max-w-lg rounded-t-3xl bg-white p-5 pb-8 shadow-2xl dark:bg-gray-800"
                >
                    <!-- Handle bar -->
                    <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-gray-300 dark:bg-gray-600"></div>

                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">Menu Lainnya</h2>
                        <button @click="showMoreMenu = false" class="rounded-full p-1 active:scale-90">
                            <X class="size-5 text-muted" />
                        </button>
                    </div>

                    <!-- Menu Grid -->
                    <div class="grid grid-cols-3 gap-3">
                        <button
                            v-for="item in moreMenuItems"
                            :key="item.name"
                            @click="goTo(item.path)"
                            class="flex flex-col items-center gap-2 rounded-2xl bg-gray-50 p-4 transition-all active:scale-95 dark:bg-gray-700"
                        >
                            <div class="flex size-11 items-center justify-center rounded-xl bg-white shadow-sm dark:bg-gray-600">
                                <component :is="item.icon" class="size-5 text-primary" />
                            </div>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ item.label }}</span>
                        </button>
                    </div>

                    <!-- Logout -->
                    <button
                        @click="handleLogout"
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-danger/20 py-3 text-sm font-bold text-danger transition-all active:scale-[0.98] dark:border-danger/30"
                    >
                        <LogOut class="size-4" />
                        Keluar
                    </button>
                </div>
            </Transition>
        </Teleport>

        <!-- Receipt Scanner -->
        <ReceiptScanner
            v-if="showScanner"
            @close="showScanner = false"
            @saved="onScanSaved"
        />
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
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-up-enter-from,
.slide-up-leave-to {
    transform: translateY(100%);
    opacity: 0;
}
</style>
