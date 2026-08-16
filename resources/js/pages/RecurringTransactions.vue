<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRecurringStore } from '@/stores/recurring';
import { useCategoryStore } from '@/stores/categories';
import { useAccountStore } from '@/stores/accounts';
import { formatCurrency, formatDate } from '@/lib/utils';
import { Plus, X, Repeat } from '@lucide/vue';

const store = useRecurringStore();
const categoryStore = useCategoryStore();
const accountStore = useAccountStore();
const showForm = ref(false);
const editingId = ref(null);
const formError = ref('');

const frequencies = [
    { value: 'daily', label: 'Harian' },
    { value: 'weekly', label: 'Mingguan' },
    { value: 'monthly', label: 'Bulanan' },
    { value: 'yearly', label: 'Tahunan' },
];

const form = ref({
    category_id: '',
    account_id: '',
    type: 'expense',
    amount: '',
    description: '',
    frequency: 'monthly',
    interval: 1,
    start_date: new Date().toISOString().split('T')[0],
    end_date: '',
});

const categories = ref([]);

onMounted(async () => {
    await store.fetchAll();
    await accountStore.fetchAll();
    await categoryStore.fetchAll('expense');
    categories.value = categoryStore.categories;
});

watch(() => form.value.type, async (type) => {
    await categoryStore.fetchAll(type);
    categories.value = categoryStore.categories;
});

function formatDateForInput(date) {
    if (!date) return '';
    return new Date(date).toISOString().split('T')[0];
}

function openForm() {
    editingId.value = null;
    formError.value = '';
    form.value = {
        category_id: '',
        account_id: '',
        type: 'expense',
        amount: '',
        description: '',
        frequency: 'monthly',
        interval: 1,
        start_date: new Date().toISOString().split('T')[0],
        end_date: '',
    };
    showForm.value = true;
}

function openEdit(r) {
    editingId.value = r.id;
    formError.value = '';
    form.value = {
        category_id: r.category_id,
        account_id: r.account_id || '',
        type: r.type,
        amount: r.amount,
        description: r.description || '',
        frequency: r.frequency,
        interval: r.interval,
        start_date: formatDateForInput(r.start_date),
        end_date: r.end_date ? formatDateForInput(r.end_date) : '',
    };
    showForm.value = true;
}

function getFrequencyLabel(f) {
    return frequencies.find((fr) => fr.value === f)?.label || f;
}

async function handleSubmit() {
    formError.value = '';
    try {
        const data = { ...form.value };
        if (!data.account_id) delete data.account_id;
        if (!data.end_date) delete data.end_date;

        if (editingId.value) {
            await store.update(editingId.value, data);
        } else {
            await store.create(data);
        }
        showForm.value = false;
    } catch (e) {
        formError.value = e.response?.data?.message || 'Terjadi kesalahan. Coba lagi.';
    }
}

async function handleDelete(id) {
    if (confirm('Hapus transaksi berulang ini?')) {
        try {
            await store.remove(id);
        } catch (e) {
            alert(e.response?.data?.message || 'Gagal menghapus.');
        }
    }
}

async function toggleActive(r) {
    await store.update(r.id, { is_active: !r.is_active });
}
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-extrabold text-gray-900 dark:text-white">Transaksi Berulang</h1>
            <button
                @click="openForm()"
                class="flex items-center gap-1 rounded-full bg-primary px-4 py-2 text-xs font-bold text-white shadow-md transition-all active:scale-95"
            >
                <Plus class="size-4" />
                Tambah
            </button>
        </div>

        <!-- Loading -->
        <div v-if="store.loading" class="flex items-center justify-center py-12">
            <div class="size-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
        </div>

        <!-- Empty -->
        <div v-else-if="!store.recurring.length" class="rounded-2xl bg-white p-8 text-center shadow-sm dark:bg-gray-800">
            <Repeat class="mx-auto size-10 text-gray-300" />
            <p class="mt-2 text-sm font-bold text-gray-900 dark:text-white">Belum ada transaksi berulang</p>
            <p class="mt-1 text-xs text-muted">Buat transaksi otomatis yang berulang</p>
        </div>

        <!-- List -->
        <div v-else class="space-y-3">
            <div
                v-for="r in store.recurring"
                :key="r.id"
                class="rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800"
            >
                <div class="flex items-center gap-3">
                    <span
                        class="flex size-11 shrink-0 items-center justify-center rounded-xl text-base"
                        :style="{
                            backgroundColor: (r.category?.color || '#e2e8f0') + '20',
                            color: r.category?.color || '#64748b',
                        }"
                    >
                        {{ r.category?.icon || '📦' }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ r.category?.name }}</p>
                            <span
                                v-if="!r.is_active"
                                class="rounded-full bg-gray-100 px-2 py-0.5 text-[9px] font-bold text-muted dark:bg-gray-700 dark:text-gray-400"
                            >
                                Nonaktif
                            </span>
                        </div>
                        <p class="text-[11px] text-muted">
                            {{ getFrequencyLabel(r.frequency) }} · {{ r.account?.name || 'Tunai' }}
                        </p>
                        <p v-if="r.description" class="mt-0.5 truncate text-[11px] text-muted">{{ r.description }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p
                            class="text-sm font-extrabold"
                            :class="r.type === 'income' ? 'text-success' : 'text-danger'"
                        >
                            {{ r.type === 'income' ? '+' : '-' }}{{ formatCurrency(r.amount) }}
                        </p>
                        <p class="text-[10px] text-muted">Berikutnya: {{ formatDate(r.next_run_date) }}</p>
                    </div>
                </div>
                <div class="mt-3 flex justify-end gap-2">
                    <button
                        @click="toggleActive(r)"
                        class="rounded-full px-3 py-1 text-[10px] font-bold active:scale-95"
                        :class="r.is_active ? 'text-warning bg-warning/10' : 'text-success bg-success/10'"
                    >
                        {{ r.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                    <button
                        @click="openEdit(r)"
                        class="rounded-full px-3 py-1 text-[10px] font-bold text-primary bg-primary/10 active:scale-95"
                    >
                        Edit
                    </button>
                    <button
                        @click="handleDelete(r.id)"
                        class="rounded-full px-3 py-1 text-[10px] font-bold text-danger bg-danger/10 active:scale-95"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        </div>

        <!-- Form Modal -->
        <Teleport to="body">
            <Transition name="slide-up">
                <div
                    v-if="showForm"
                    class="fixed inset-0 z-[100] bg-black/40"
                    @click="showForm = false"
                >
                    <div @click.stop class="fixed inset-x-0 bottom-0 z-[101] mx-auto max-w-lg rounded-t-3xl bg-white p-5 pb-8 shadow-2xl dark:bg-gray-800">
                        <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">
                                {{ editingId ? 'Edit' : 'Tambah' }} Transaksi Berulang
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
                                    <option v-for="c in categories" :key="c.id" :value="c.id">
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

                            <!-- Frequency -->
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Frekuensi</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        v-for="f in frequencies"
                                        :key="f.value"
                                        type="button"
                                        @click="form.frequency = f.value"
                                        class="rounded-xl py-2.5 text-sm font-bold transition-all"
                                        :class="form.frequency === f.value ? 'bg-primary text-white' : 'bg-gray-100 text-muted dark:bg-gray-700 dark:text-gray-400'"
                                    >
                                        {{ f.label }}
                                    </button>
                                </div>
                            </div>

                            <!-- Interval -->
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Setiap</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        v-model="form.interval"
                                        type="number"
                                        min="1"
                                        class="w-20 rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium text-center dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    />
                                    <span class="text-sm text-muted">{{ getFrequencyLabel(form.frequency) }}</span>
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

                            <!-- Dates -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Mulai</label>
                                    <input
                                        v-model="form.start_date"
                                        type="date"
                                        class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Selesai</label>
                                    <input
                                        v-model="form.end_date"
                                        type="date"
                                        class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="w-full rounded-xl bg-primary py-3 text-sm font-extrabold text-white shadow-md transition-all active:scale-[0.98]"
                            >
                                {{ editingId ? 'Simpan Perubahan' : 'Tambah' }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
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
