<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useNotificationForwardsStore } from '@/stores/notificationForwards';
import { useCategoryStore } from '@/stores/categories';
import { useAccountStore } from '@/stores/accounts';
import { formatCurrency, formatDate } from '@/lib/utils';
import { Bell, Check, X, ChevronLeft, ChevronRight, Smartphone, Clock, CheckCircle, XCircle } from '@lucide/vue';

const store = useNotificationForwardsStore();
const categoryStore = useCategoryStore();
const accountStore = useAccountStore();

const showConfirmModal = ref(false);
const selectedForward = ref(null);
const confirmError = ref('');
const filterStatus = ref('pending');

const confirmForm = ref({
    category_id: '',
    account_id: '',
    type: 'expense',
    amount: '',
    description: '',
    date: new Date().toISOString().split('T')[0],
});

const filteredCategories = computed(() => {
    return categoryStore.categories.filter((c) => c.type === confirmForm.value.type);
});

const statusLabels = {
    pending: { label: 'Baru', color: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400', icon: Clock },
    parsed: { label: 'Siap Diproses', color: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', icon: Bell },
    confirmed: { label: 'Tercatat', color: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400', icon: CheckCircle },
    ignored: { label: 'Diabaikan', color: 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400', icon: XCircle },
};

const appNameMap = {
    'com.bca': 'BCA Mobile',
    'com.bri': 'BRImo',
    'com.bni': 'BNI Mobile Banking',
    'com.mandiri': 'Livin by Mandiri',
    'com.cimb': 'CIMB Clicks',
    'com.danamon': 'Danamon Online',
    'com.bsi': 'BSI Mobile',
    'com.mega': 'Mega Mobile',
    'com.gopay.gopayapp': 'GoPay',
    'com.gopajj.gopajj': 'OVO',
    'com.dana': 'DANA',
    'com.shopeepay': 'ShopeePay',
    'com.linkaja': 'LinkAja',
};

function getAppName(packageName) {
    return appNameMap[packageName] || packageName;
}

let refreshInterval = null;

onMounted(() => {
    categoryStore.fetchAll();
    accountStore.fetchAll();
    store.fetchAll({ status: filterStatus.value });
    store.fetchPendingCount();
    refreshInterval = setInterval(() => {
        store.fetchPendingCount();
    }, 30000);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
});

function changeFilter(status) {
    filterStatus.value = status;
    store.fetchAll({ status });
}

function openConfirmModal(forward) {
    selectedForward.value = forward;
    confirmError.value = '';
    const parsed = forward.parsed_data || {};
    confirmForm.value = {
        category_id: '',
        account_id: '',
        type: parsed.type || 'expense',
        amount: parsed.amount || '',
        description: parsed.description || '',
        date: parsed.date || new Date().toISOString().split('T')[0],
    };
    showConfirmModal.value = true;
}

async function handleConfirm() {
    confirmError.value = '';
    try {
        const data = { ...confirmForm.value };
        if (!data.account_id) delete data.account_id;
        await store.confirm(selectedForward.value.id, data);
        showConfirmModal.value = false;
        store.fetchAll({ status: filterStatus.value });
    } catch (e) {
        confirmError.value = e.response?.data?.message || 'Terjadi kesalahan.';
    }
}

async function handleIgnore(id) {
    if (confirm('Abaikan notifikasi ini?')) {
        await store.ignore(id);
        store.fetchAll({ status: filterStatus.value });
    }
}

function goToPage(page) {
    store.fetchAll({ status: filterStatus.value, page });
}
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-gray-900 dark:text-white">Notifikasi Masuk</h1>
                <p class="text-xs text-muted">Transaksi otomatis dari notifikasi HP</p>
            </div>
            <div v-if="store.pendingCount > 0" class="flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1.5 dark:bg-amber-900/30">
                <Bell class="size-3.5 text-amber-600 dark:text-amber-400" />
                <span class="text-xs font-bold text-amber-700 dark:text-amber-400">{{ store.pendingCount }} baru</span>
            </div>
        </div>

        <!-- Filter -->
        <div class="flex gap-2 overflow-x-auto pb-1">
            <button
                @click="changeFilter('')"
                class="shrink-0 rounded-full px-4 py-2 text-xs font-bold transition-all"
                :class="filterStatus === '' ? 'bg-gray-900 text-white shadow-md dark:bg-white dark:text-gray-900' : 'bg-white text-muted border border-border dark:bg-gray-800 dark:border-gray-700'"
            >
                Semua
            </button>
            <button
                @click="changeFilter('pending')"
                class="shrink-0 rounded-full px-4 py-2 text-xs font-bold transition-all"
                :class="filterStatus === 'pending' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-muted border border-border dark:bg-gray-800 dark:border-gray-700'"
            >
                Baru
            </button>
            <button
                @click="changeFilter('parsed')"
                class="shrink-0 rounded-full px-4 py-2 text-xs font-bold transition-all"
                :class="filterStatus === 'parsed' ? 'bg-blue-500 text-white shadow-md' : 'bg-white text-muted border border-border dark:bg-gray-800 dark:border-gray-700'"
            >
                Siap Proses
            </button>
            <button
                @click="changeFilter('confirmed')"
                class="shrink-0 rounded-full px-4 py-2 text-xs font-bold transition-all"
                :class="filterStatus === 'confirmed' ? 'bg-green-500 text-white shadow-md' : 'bg-white text-muted border border-border dark:bg-gray-800 dark:border-gray-700'"
            >
                Tercatat
            </button>
            <button
                @click="changeFilter('ignored')"
                class="shrink-0 rounded-full px-4 py-2 text-xs font-bold transition-all"
                :class="filterStatus === 'ignored' ? 'bg-gray-500 text-white shadow-md' : 'bg-white text-muted border border-border dark:bg-gray-800 dark:border-gray-700'"
            >
                Diabaikan
            </button>
        </div>

        <!-- Loading -->
        <div v-if="store.loading" class="flex items-center justify-center py-12">
            <div class="size-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
        </div>

        <!-- Empty -->
        <div v-else-if="!store.forwards.length" class="rounded-2xl bg-white p-8 text-center shadow-sm dark:bg-gray-800">
            <Smartphone class="mx-auto size-10 text-gray-300" />
            <p class="mt-2 text-sm font-bold text-gray-900 dark:text-white">Belum ada notifikasi</p>
            <p class="mt-1 text-xs text-muted">Pasang Android Companion App untuk mendeteksi notifikasi secara otomatis</p>
        </div>

        <!-- List -->
        <div v-else class="space-y-2">
            <div
                v-for="f in store.forwards"
                :key="f.id"
                class="rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800"
            >
                <!-- Header -->
                <div class="mb-2 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Smartphone class="size-4 text-muted" />
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ getAppName(f.package_name) }}</span>
                    </div>
                    <span
                        class="flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold"
                        :class="statusLabels[f.status]?.color"
                    >
                        <component :is="statusLabels[f.status]?.icon" class="size-3" />
                        {{ statusLabels[f.status]?.label }}
                    </span>
                </div>

                <!-- Notification Text -->
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ f.title }}</p>
                <p class="mt-0.5 text-xs text-muted line-clamp-2">{{ f.message }}</p>

                <!-- Parsed Data -->
                <div v-if="f.parsed_data && f.status === 'parsed'" class="mt-3 rounded-xl bg-blue-50 p-3 dark:bg-blue-900/20">
                    <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Hasil Deteksi AI</p>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-muted">Jenis:</span>
                            <span
                                class="ml-1 font-bold"
                                :class="f.parsed_data.type === 'income' ? 'text-success' : 'text-danger'"
                            >
                                {{ f.parsed_data.type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-muted">Jumlah:</span>
                            <span class="ml-1 font-bold text-gray-900 dark:text-white">{{ formatCurrency(f.parsed_data.amount) }}</span>
                        </div>
                        <div v-if="f.parsed_data.merchant" class="col-span-2">
                            <span class="text-muted">Merchant:</span>
                            <span class="ml-1 font-bold text-gray-900 dark:text-white">{{ f.parsed_data.merchant }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-muted">Deskripsi:</span>
                            <span class="ml-1 font-bold text-gray-900 dark:text-white">{{ f.parsed_data.description }}</span>
                        </div>
                    </div>
                </div>

                <!-- Timestamp -->
                <p class="mt-2 text-[10px] text-muted">{{ formatDate(f.created_at) }}</p>

                <!-- Actions -->
                <div v-if="f.status === 'parsed'" class="mt-3 flex gap-2">
                    <button
                        @click="openConfirmModal(f)"
                        class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-primary py-2.5 text-xs font-bold text-white shadow-sm transition-all active:scale-[0.98]"
                    >
                        <Check class="size-3.5" />
                        Catat Transaksi
                    </button>
                    <button
                        @click="handleIgnore(f.id)"
                        class="flex items-center justify-center rounded-xl border border-border bg-white px-4 py-2.5 text-xs font-bold text-muted transition-all active:scale-[0.98] dark:border-gray-600 dark:bg-gray-700"
                    >
                        <X class="size-3.5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="store.pagination?.last_page > 1" class="flex items-center justify-center gap-3 py-2">
            <button
                @click="goToPage(store.pagination.current_page - 1)"
                :disabled="store.pagination.current_page <= 1"
                class="rounded-full bg-white p-2 shadow-sm disabled:opacity-30 active:scale-95 dark:bg-gray-800"
            >
                <ChevronLeft class="size-4" />
            </button>
            <span class="text-sm font-bold text-gray-900 dark:text-white">
                {{ store.pagination.current_page }} / {{ store.pagination.last_page }}
            </span>
            <button
                @click="goToPage(store.pagination.current_page + 1)"
                :disabled="store.pagination.current_page >= store.pagination.last_page"
                class="rounded-full bg-white p-2 shadow-sm disabled:opacity-30 active:scale-95 dark:bg-gray-800"
            >
                <ChevronRight class="size-4" />
            </button>
        </div>

        <!-- Confirm Modal -->
        <Teleport to="body">
            <Transition name="fade">
                <div
                    v-if="showConfirmModal"
                    class="fixed inset-0 z-[100] bg-black/40"
                    @click="showConfirmModal = false"
                ></div>
            </Transition>
            <Transition name="slide-up">
                <div
                    v-if="showConfirmModal"
                    class="fixed inset-x-0 bottom-0 z-[101] mx-auto max-w-lg rounded-t-3xl bg-white p-5 pb-8 shadow-2xl dark:bg-gray-800"
                >
                    <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">Konfirmasi Transaksi</h2>
                        <button @click="showConfirmModal = false" class="rounded-full p-1 active:scale-90">
                            <X class="size-5 text-muted" />
                        </button>
                    </div>

                    <p v-if="confirmError" class="mb-4 rounded-xl bg-red-50 px-4 py-2.5 text-xs font-medium text-red-600 dark:bg-red-900/20 dark:text-red-400">
                        {{ confirmError }}
                    </p>

                    <!-- Original Notification -->
                    <div class="mb-4 rounded-xl bg-gray-50 p-3 dark:bg-gray-700/50">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-muted">Notifikasi Asli</p>
                        <p class="mt-1 text-xs text-gray-700 dark:text-gray-300">{{ selectedForward?.message }}</p>
                    </div>

                    <form @submit.prevent="handleConfirm" class="space-y-4">
                        <!-- Type -->
                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="confirmForm.type = 'expense'"
                                class="flex-1 rounded-xl py-2.5 text-sm font-bold transition-all"
                                :class="confirmForm.type === 'expense' ? 'bg-danger text-white' : 'bg-gray-100 text-muted dark:bg-gray-700 dark:text-gray-400'"
                            >
                                Pengeluaran
                            </button>
                            <button
                                type="button"
                                @click="confirmForm.type = 'income'"
                                class="flex-1 rounded-xl py-2.5 text-sm font-bold transition-all"
                                :class="confirmForm.type === 'income' ? 'bg-success text-white' : 'bg-gray-100 text-muted dark:bg-gray-700 dark:text-gray-400'"
                            >
                                Pemasukan
                            </button>
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Kategori</label>
                            <select
                                v-model="confirmForm.category_id"
                                class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                required
                            >
                                <option value="" disabled>Pilih kategori</option>
                                <option v-for="c in filteredCategories" :key="c.id" :value="c.id">
                                    {{ c.icon }} {{ c.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Account -->
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Akun</label>
                            <select
                                v-model="confirmForm.account_id"
                                class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Tidak ada</option>
                                <option v-for="a in accountStore.accounts" :key="a.id" :value="a.id">
                                    {{ a.icon || '💰' }} {{ a.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Jumlah</label>
                            <input
                                v-model="confirmForm.amount"
                                type="number"
                                min="1"
                                placeholder="0"
                                class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                required
                            />
                        </div>

                        <!-- Date -->
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Tanggal</label>
                            <input
                                v-model="confirmForm.date"
                                type="date"
                                class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                required
                            />
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Catatan</label>
                            <input
                                v-model="confirmForm.description"
                                type="text"
                                placeholder="Opsional"
                                class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-xl bg-primary py-3 text-sm font-extrabold text-white shadow-md transition-all active:scale-[0.98]"
                        >
                            Catat Transaksi
                        </button>
                    </form>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
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
