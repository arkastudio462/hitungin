<script setup>
import { ref, onMounted } from 'vue';
import { useBudgetStore } from '@/stores/budgets';
import { useCategoryStore } from '@/stores/categories';
import { formatCurrency } from '@/lib/utils';
import { Plus, X, PiggyBank } from '@lucide/vue';

const store = useBudgetStore();
const categoryStore = useCategoryStore();
const showForm = ref(false);
const editingId = ref(null);
const formError = ref('');

const form = ref({
    category_id: '',
    amount: '',
    period: 'monthly',
    start_date: '',
    end_date: '',
});

const categories = ref([]);

onMounted(async () => {
    await store.fetchAll();
    await categoryStore.fetchAll('expense');
    categories.value = categoryStore.categories;
});

function formatDateForInput(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toISOString().split('T')[0];
}

function openForm() {
    editingId.value = null;
    formError.value = '';
    const now = new Date();
    form.value = {
        category_id: '',
        amount: '',
        period: 'monthly',
        start_date: new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0],
        end_date: new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0],
    };
    showForm.value = true;
}

function openEdit(b) {
    editingId.value = b.id;
    formError.value = '';
    form.value = {
        category_id: b.category_id,
        amount: b.amount,
        period: b.period,
        start_date: formatDateForInput(b.start_date),
        end_date: formatDateForInput(b.end_date),
    };
    showForm.value = true;
}

async function handleSubmit() {
    formError.value = '';
    try {
        if (editingId.value) {
            await store.update(editingId.value, form.value);
        } else {
            await store.create(form.value);
        }
        showForm.value = false;
        store.fetchAll();
    } catch (e) {
        formError.value = e.response?.data?.message || 'Terjadi kesalahan. Coba lagi.';
    }
}

async function handleDelete(id) {
    if (confirm('Hapus anggaran ini?')) {
        try {
            await store.remove(id);
        } catch (e) {
            alert(e.response?.data?.message || 'Gagal menghapus anggaran.');
        }
    }
}

function getSpent(budget) {
    return budget.spent || 0;
}

function getPercentage(budget) {
    const spent = getSpent(budget);
    return Math.min((spent / budget.amount) * 100, 100);
}
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-extrabold text-gray-900 dark:text-white">Anggaran</h1>
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
        <div v-else-if="!store.budgets.length" class="rounded-2xl bg-white p-8 text-center shadow-sm dark:bg-gray-800">
            <PiggyBank class="mx-auto size-10 text-gray-300 dark:text-gray-600" />
            <p class="mt-2 text-sm font-bold text-gray-900 dark:text-white">Belum ada anggaran</p>
            <p class="mt-1 text-xs text-muted">Buat anggaran untuk mengontrol pengeluaran</p>
        </div>

        <!-- Budget Cards -->
        <div v-else class="space-y-3">
            <div
                v-for="b in store.budgets"
                :key="b.id"
                class="rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800"
            >
                <div class="flex items-center gap-3">
                    <span
                        class="flex size-11 shrink-0 items-center justify-center rounded-xl text-base"
                        :style="{
                            backgroundColor: (b.category?.color || '#e2e8f0') + '20',
                            color: b.category?.color || '#64748b',
                        }"
                    >
                        {{ b.category?.icon || '📦' }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between">
                            <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ b.category?.name }}</p>
                            <span class="shrink-0 rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-[10px] font-bold text-muted">
                                {{ b.period === 'monthly' ? 'Bulanan' : 'Tahunan' }}
                            </span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                            <div
                                class="h-full rounded-full transition-all duration-500"
                                :class="getPercentage(b) >= 90 ? 'bg-danger' : getPercentage(b) >= 70 ? 'bg-warning' : 'bg-primary'"
                                :style="{ width: `${getPercentage(b)}%` }"
                            ></div>
                        </div>
                        <div class="mt-1.5 flex items-center justify-between text-[11px]">
                            <span class="text-muted">
                                {{ formatCurrency(getSpent(b)) }} / {{ formatCurrency(b.amount) }}
                            </span>
                            <span
                                class="font-bold"
                                :class="getPercentage(b) >= 90 ? 'text-danger' : getPercentage(b) >= 70 ? 'text-warning' : 'text-primary'"
                            >
                                {{ Math.round(getPercentage(b)) }}%
                            </span>
                        </div>
                    </div>
                </div>
                <div class="mt-3 flex justify-end gap-2">
                    <button
                        @click="openEdit(b)"
                        class="rounded-full px-3 py-1 text-[10px] font-bold text-primary bg-primary/10 active:scale-95"
                    >
                        Edit
                    </button>
                    <button
                        @click="handleDelete(b.id)"
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
                                {{ editingId ? 'Edit' : 'Tambah' }} Anggaran
                            </h2>
                            <button @click="showForm = false" class="rounded-full p-1 active:scale-90">
                                <X class="size-5 text-muted" />
                            </button>
                        </div>

                        <p v-if="formError" class="mb-4 rounded-xl bg-red-50 px-4 py-2.5 text-xs font-medium text-red-600 dark:bg-red-900/20 dark:text-red-400">
                            {{ formError }}
                        </p>

                        <form @submit.prevent="handleSubmit" class="space-y-4">
                            <!-- Category -->
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Kategori</label>
                                <select
                                    v-model="form.category_id"
                                    class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    required
                                >
                                    <option value="" disabled>Pilih kategori</option>
                                    <option v-for="c in categories" :key="c.id" :value="c.id">
                                        {{ c.icon }} {{ c.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Amount -->
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Batas Anggaran</label>
                                <input
                                    v-model="form.amount"
                                    type="number"
                                    min="1"
                                    placeholder="0"
                                    class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    required
                                />
                            </div>

                            <!-- Period -->
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    @click="form.period = 'monthly'"
                                    class="flex-1 rounded-xl py-2.5 text-sm font-bold transition-all"
                                    :class="form.period === 'monthly' ? 'bg-primary text-white' : 'bg-gray-100 text-muted dark:bg-gray-700'"
                                >
                                    Bulanan
                                </button>
                                <button
                                    type="button"
                                    @click="form.period = 'yearly'"
                                    class="flex-1 rounded-xl py-2.5 text-sm font-bold transition-all"
                                    :class="form.period === 'yearly' ? 'bg-primary text-white' : 'bg-gray-100 text-muted dark:bg-gray-700'"
                                >
                                    Tahunan
                                </button>
                            </div>

                            <!-- Dates -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Dari</label>
                                    <input
                                        v-model="form.start_date"
                                        type="date"
                                        class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Sampai</label>
                                    <input
                                        v-model="form.end_date"
                                        type="date"
                                        class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        required
                                    />
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="w-full rounded-xl bg-primary py-3 text-sm font-extrabold text-white shadow-md transition-all active:scale-[0.98]"
                            >
                                {{ editingId ? 'Simpan Perubahan' : 'Tambah Anggaran' }}
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
