<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useTransactionStore } from '@/stores/transactions';
import { useCategoryStore } from '@/stores/categories';
import { useAccountStore } from '@/stores/accounts';
import { useTagStore } from '@/stores/tags';
import { formatCurrency, formatDate } from '@/lib/utils';
import { Plus, X, TrendingUp, TrendingDown, ChevronLeft, ChevronRight, Search } from '@lucide/vue';

const store = useTransactionStore();
const categoryStore = useCategoryStore();
const accountStore = useAccountStore();
const tagStore = useTagStore();
const showForm = ref(false);
const filterType = ref('');
const editingId = ref(null);
const formError = ref('');
const searchQuery = ref('');

const form = ref({
    category_id: '',
    account_id: '',
    type: 'expense',
    amount: '',
    description: '',
    date: new Date().toISOString().split('T')[0],
    tags: [],
});

const filteredCategories = computed(() => {
    return categoryStore.categories.filter((c) => c.type === form.value.type);
});

onMounted(() => {
    store.fetchAll();
    categoryStore.fetchAll();
    accountStore.fetchAll();
    tagStore.fetchAll();
});

watch(filterType, (val) => {
    store.fetchAll(val ? { type: val } : {});
});

let searchTimeout = null;
watch(searchQuery, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        store.fetchAll({
            type: filterType.value || undefined,
            search: val || undefined,
        });
    }, 300);
});

function formatDateForInput(date) {
    if (!date) return new Date().toISOString().split('T')[0];
    const d = new Date(date);
    return d.toISOString().split('T')[0];
}

function openForm(type = 'expense') {
    editingId.value = null;
    formError.value = '';
    form.value = {
        category_id: '',
        account_id: '',
        type,
        amount: '',
        description: '',
        date: new Date().toISOString().split('T')[0],
        tags: [],
    };
    showForm.value = true;
}

function openEdit(t) {
    editingId.value = t.id;
    formError.value = '';
    form.value = {
        category_id: t.category_id,
        account_id: t.account_id || '',
        type: t.type,
        amount: t.amount,
        description: t.description || '',
        date: formatDateForInput(t.date),
        tags: t.tags?.map((tag) => tag.id) || [],
    };
    showForm.value = true;
}

function toggleTag(tagId) {
    const idx = form.value.tags.indexOf(tagId);
    if (idx === -1) {
        form.value.tags.push(tagId);
    } else {
        form.value.tags.splice(idx, 1);
    }
}

async function handleSubmit() {
    formError.value = '';
    try {
        const data = { ...form.value };
        if (!data.account_id) delete data.account_id;
        if (!data.tags.length) delete data.tags;

        if (editingId.value) {
            await store.update(editingId.value, data);
        } else {
            await store.create(data);
        }
        showForm.value = false;
        store.fetchAll(filterType.value ? { type: filterType.value } : {});
    } catch (e) {
        formError.value = e.response?.data?.message || 'Terjadi kesalahan. Coba lagi.';
    }
}

async function handleDelete(id) {
    if (confirm('Hapus transaksi ini?')) {
        try {
            await store.remove(id);
            store.fetchAll(filterType.value ? { type: filterType.value } : {});
        } catch (e) {
            alert(e.response?.data?.message || 'Gagal menghapus transaksi.');
        }
    }
}

function goToPage(page) {
    store.fetchAll({ type: filterType.value || undefined, search: searchQuery.value || undefined, page });
}
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-extrabold text-gray-900 dark:text-white">Transaksi</h1>
            <button
                @click="openForm()"
                class="flex items-center gap-1 rounded-full bg-primary px-4 py-2 text-xs font-bold text-white shadow-md transition-all active:scale-95"
            >
                <Plus class="size-4" />
                Tambah
            </button>
        </div>

        <!-- Search -->
        <div class="relative">
            <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted" />
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari transaksi..."
                class="w-full rounded-xl border-2 border-border bg-white py-2.5 pl-10 pr-4 text-sm font-medium dark:bg-gray-800 dark:border-gray-700"
            />
        </div>

        <!-- Filter -->
        <div class="flex gap-2">
            <button
                @click="filterType = ''"
                class="rounded-full px-4 py-2 text-xs font-bold transition-all"
                :class="filterType === '' ? 'bg-gray-900 text-white shadow-md dark:bg-white dark:text-gray-900' : 'bg-white text-muted border border-border dark:bg-gray-800 dark:border-gray-700'"
            >
                Semua
            </button>
            <button
                @click="filterType = 'income'"
                class="rounded-full px-4 py-2 text-xs font-bold transition-all"
                :class="filterType === 'income' ? 'bg-success text-white shadow-md' : 'bg-white text-muted border border-border dark:bg-gray-800 dark:border-gray-700'"
            >
                Masuk
            </button>
            <button
                @click="filterType = 'expense'"
                class="rounded-full px-4 py-2 text-xs font-bold transition-all"
                :class="filterType === 'expense' ? 'bg-danger text-white shadow-md' : 'bg-white text-muted border border-border dark:bg-gray-800 dark:border-gray-700'"
            >
                Keluar
            </button>
        </div>

        <!-- Loading -->
        <div v-if="store.loading" class="flex items-center justify-center py-12">
            <div class="size-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
        </div>

        <!-- Empty -->
        <div v-else-if="!store.transactions.length" class="rounded-2xl bg-white p-8 text-center shadow-sm dark:bg-gray-800">
            <TrendingUp class="mx-auto size-10 text-gray-300" />
            <p class="mt-2 text-sm font-bold text-gray-900 dark:text-white">Belum ada transaksi</p>
            <p class="mt-1 text-xs text-muted">Tekan tombol Tambah untuk mencatat</p>
        </div>

        <!-- List -->
        <div v-else class="space-y-2">
            <div
                v-for="t in store.transactions"
                :key="t.id"
                class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm active:bg-gray-50 dark:bg-gray-800 dark:active:bg-gray-700"
                @click="openEdit(t)"
            >
                <span
                    class="flex size-11 shrink-0 items-center justify-center rounded-xl text-base"
                    :style="{
                        backgroundColor: (t.category?.color || '#e2e8f0') + '20',
                        color: t.category?.color || '#64748b',
                    }"
                >
                    {{ t.category?.icon || '📦' }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ t.category?.name }}</p>
                    <div class="flex items-center gap-2">
                        <p class="text-[11px] text-muted">{{ formatDate(t.date) }}</p>
                        <span v-if="t.account" class="text-[10px] text-muted">· {{ t.account.name }}</span>
                    </div>
                    <div v-if="t.tags?.length" class="mt-1 flex flex-wrap gap-1">
                        <span
                            v-for="tag in t.tags"
                            :key="tag.id"
                            class="rounded-full px-2 py-0.5 text-[9px] font-bold text-white"
                            :style="{ backgroundColor: tag.color || '#64748b' }"
                        >
                            {{ tag.name }}
                        </span>
                    </div>
                    <p v-if="t.description" class="mt-0.5 truncate text-[11px] text-muted">{{ t.description }}</p>
                </div>
                <div class="shrink-0 text-right">
                    <p
                        class="text-sm font-extrabold"
                        :class="t.type === 'income' ? 'text-success' : 'text-danger'"
                    >
                        {{ t.type === 'income' ? '+' : '-' }}{{ formatCurrency(t.amount) }}
                    </p>
                    <button
                        @click.stop="handleDelete(t.id)"
                        class="mt-1 text-[10px] font-semibold text-danger/60 active:text-danger"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="store.pagination.last_page > 1" class="flex items-center justify-center gap-3 py-2">
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

        <!-- Form Modal -->
        <Teleport to="body">
            <Transition name="fade">
                <div
                    v-if="showForm"
                    class="fixed inset-0 z-[100] bg-black/40"
                    @click="showForm = false"
                ></div>
            </Transition>
            <Transition name="slide-up">
                <div
                    v-if="showForm"
                    class="fixed inset-x-0 bottom-0 z-[101] mx-auto max-w-lg rounded-t-3xl bg-white p-5 pb-8 shadow-2xl dark:bg-gray-800"
                >
                    <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">
                            {{ editingId ? 'Edit' : 'Tambah' }} Transaksi
                        </h2>
                        <button @click="showForm = false" class="rounded-full p-1 active:scale-90">
                            <X class="size-5 text-muted" />
                        </button>
                    </div>

                    <p v-if="formError" class="mb-4 rounded-xl bg-red-50 px-4 py-2.5 text-xs font-medium text-red-600 dark:bg-red-900/20 dark:text-red-400">
                        {{ formError }}
                    </p>

                    <form @submit.prevent="handleSubmit" class="space-y-4">
                        <!-- Type -->
                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="form.type = 'expense'"
                                class="flex-1 rounded-xl py-2.5 text-sm font-bold transition-all"
                                :class="form.type === 'expense' ? 'bg-danger text-white' : 'bg-gray-100 text-muted dark:bg-gray-700 dark:text-gray-400'"
                            >
                                Pengeluaran
                            </button>
                            <button
                                type="button"
                                @click="form.type = 'income'"
                                class="flex-1 rounded-xl py-2.5 text-sm font-bold transition-all"
                                :class="form.type === 'income' ? 'bg-success text-white' : 'bg-gray-100 text-muted dark:bg-gray-700 dark:text-gray-400'"
                            >
                                Pemasukan
                            </button>
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Kategori</label>
                            <select
                                v-model="form.category_id"
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
                                v-model="form.account_id"
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
                                v-model="form.amount"
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
                                v-model="form.date"
                                type="date"
                                class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                required
                            />
                        </div>

                        <!-- Tags -->
                        <div v-if="tagStore.tags.length">
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Tag</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="tag in tagStore.tags"
                                    :key="tag.id"
                                    type="button"
                                    @click="toggleTag(tag.id)"
                                    class="rounded-full px-3 py-1 text-[11px] font-bold transition-all"
                                    :class="form.tags.includes(tag.id)
                                        ? 'text-white'
                                        : 'bg-gray-100 text-muted dark:bg-gray-700 dark:text-gray-400'"
                                    :style="form.tags.includes(tag.id) ? { backgroundColor: tag.color || '#64748b' } : {}"
                                >
                                    {{ tag.name }}
                                </button>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Catatan</label>
                            <input
                                v-model="form.description"
                                type="text"
                                placeholder="Opsional"
                                class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-xl bg-primary py-3 text-sm font-extrabold text-white shadow-md transition-all active:scale-[0.98]"
                        >
                            {{ editingId ? 'Simpan Perubahan' : 'Tambah Transaksi' }}
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
