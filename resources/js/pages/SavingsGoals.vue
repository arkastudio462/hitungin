<script setup>
import { ref, onMounted, computed } from 'vue';
import { useSavingsGoalStore } from '@/stores/savingsGoals';
import { useAccountStore } from '@/stores/accounts';
import { formatCurrency, formatDate } from '@/lib/utils';
import { Plus, X, Target, Check } from '@lucide/vue';

const store = useSavingsGoalStore();
const accountStore = useAccountStore();
const showForm = ref(false);
const showDeposit = ref(false);
const depositGoalId = ref(null);
const depositAmount = ref('');
const editingId = ref(null);
const formError = ref('');

const icons = ['🏖️', '🚗', '🏠', '💻', '📱', '✈️', '🎓', '💍', '🏥', '🎁'];
const colors = ['#2563eb', '#16a34a', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316', '#ef4444'];

const form = ref({
    name: '',
    target_amount: '',
    account_id: '',
    target_date: '',
    icon: '🎯',
    color: '#2563eb',
});

onMounted(async () => {
    await store.fetchAll();
    await accountStore.fetchAll();
});

function openForm() {
    editingId.value = null;
    formError.value = '';
    form.value = { name: '', target_amount: '', account_id: '', target_date: '', icon: '🎯', color: '#2563eb' };
    showForm.value = true;
}

function openEdit(g) {
    editingId.value = g.id;
    formError.value = '';
    form.value = {
        name: g.name,
        target_amount: g.target_amount,
        account_id: g.account_id || '',
        target_date: g.target_date ? new Date(g.target_date).toISOString().split('T')[0] : '',
        icon: g.icon || '🎯',
        color: g.color || '#2563eb',
    };
    showForm.value = true;
}

function openDeposit(id) {
    depositGoalId.value = id;
    depositAmount.value = '';
    showDeposit.value = true;
}

function getProgress(g) {
    const target = Number(g.target_amount);
    const current = Number(g.current_amount);
    return target > 0 ? Math.min((current / target) * 100, 100) : 0;
}

function getDaysLeft(g) {
    if (!g.target_date) return null;
    const diff = new Date(g.target_date) - new Date();
    return Math.max(0, Math.ceil(diff / (1000 * 60 * 60 * 24)));
}

async function handleSubmit() {
    formError.value = '';
    try {
        const data = { ...form.value };
        if (!data.account_id) delete data.account_id;
        if (!data.target_date) delete data.target_date;

        if (editingId.value) {
            await store.update(editingId.value, data);
        } else {
            await store.create(data);
        }
        showForm.value = false;
    } catch (e) {
        formError.value = e.response?.data?.message || 'Terjadi kesalahan.';
    }
}

async function handleDeposit() {
    if (!depositAmount.value || Number(depositAmount.value) <= 0) return;
    try {
        await store.deposit(depositGoalId.value, Number(depositAmount.value));
        showDeposit.value = false;
    } catch (e) {
        alert(e.response?.data?.message || 'Gagal menabung.');
    }
}

async function handleDelete(id) {
    if (confirm('Hapus target tabungan ini?')) {
        try {
            await store.remove(id);
        } catch (e) {
            alert(e.response?.data?.message || 'Gagal menghapus.');
        }
    }
}
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-extrabold text-gray-900 dark:text-white">Target Tabungan</h1>
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
        <div v-else-if="!store.goals.length" class="rounded-2xl bg-white p-8 text-center shadow-sm dark:bg-gray-800">
            <Target class="mx-auto size-10 text-gray-300" />
            <p class="mt-2 text-sm font-bold text-gray-900 dark:text-white">Belum ada target</p>
            <p class="mt-1 text-xs text-muted">Buat target untuk mencapai impianmu</p>
        </div>

        <!-- Goals -->
        <div v-else class="space-y-3">
            <div
                v-for="g in store.goals"
                :key="g.id"
                class="rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800"
            >
                <div class="flex items-center gap-3">
                    <span
                        class="flex size-11 shrink-0 items-center justify-center rounded-xl text-base"
                        :style="{
                            backgroundColor: (g.color || '#e2e8f0') + '20',
                            color: g.color || '#64748b',
                        }"
                    >
                        {{ g.icon || '🎯' }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ g.name }}</p>
                            <Check v-if="g.is_completed" class="size-4 text-success" />
                        </div>
                        <p class="text-[11px] text-muted">
                            {{ formatCurrency(g.current_amount) }} / {{ formatCurrency(g.target_amount) }}
                        </p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-sm font-extrabold" :class="g.is_completed ? 'text-success' : 'text-primary'">
                            {{ Math.round(getProgress(g)) }}%
                        </p>
                        <p v-if="getDaysLeft(g) !== null && !g.is_completed" class="text-[10px] text-muted">
                            {{ getDaysLeft(g) }} hari lagi
                        </p>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                    <div
                        class="h-full rounded-full transition-all duration-500"
                        :class="g.is_completed ? 'bg-success' : 'bg-primary'"
                        :style="{ width: `${getProgress(g)}%` }"
                    ></div>
                </div>

                <div class="mt-3 flex justify-end gap-2">
                    <button
                        v-if="!g.is_completed"
                        @click="openDeposit(g.id)"
                        class="rounded-full px-3 py-1 text-[10px] font-bold text-success bg-success/10 active:scale-95"
                    >
                        Tabung
                    </button>
                    <button
                        @click="openEdit(g)"
                        class="rounded-full px-3 py-1 text-[10px] font-bold text-primary bg-primary/10 active:scale-95"
                    >
                        Edit
                    </button>
                    <button
                        @click="handleDelete(g.id)"
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
                                {{ editingId ? 'Edit' : 'Tambah' }} Target
                            </h2>
                            <button @click="showForm = false" class="rounded-full p-1 active:scale-90">
                                <X class="size-5 text-muted" />
                            </button>
                        </div>

                        <p v-if="formError" class="mb-4 rounded-xl bg-red-50 px-4 py-2.5 text-xs font-medium text-red-600 dark:bg-red-900/20 dark:text-red-400">
                            {{ formError }}
                        </p>

                        <form @submit.prevent="handleSubmit" class="space-y-4">
                            <!-- Name -->
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Nama Target</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Contoh: Liburan ke Bali"
                                    class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    required
                                />
                            </div>

                            <!-- Target Amount -->
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Target Jumlah</label>
                                <input
                                    v-model="form.target_amount"
                                    type="number"
                                    min="1"
                                    placeholder="0"
                                    class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    required
                                />
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

                            <!-- Target Date -->
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Tanggal Target</label>
                                <input
                                    v-model="form.target_date"
                                    type="date"
                                    class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                            </div>

                            <!-- Icon -->
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Ikon</label>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="icon in icons"
                                        :key="icon"
                                        type="button"
                                        @click="form.icon = icon"
                                        class="flex size-10 items-center justify-center rounded-xl text-lg transition-all"
                                        :class="form.icon === icon ? 'bg-primary/10 ring-2 ring-primary scale-110' : 'bg-gray-50 dark:bg-gray-700'"
                                    >
                                        {{ icon }}
                                    </button>
                                </div>
                            </div>

                            <!-- Color -->
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Warna</label>
                                <div class="flex gap-2">
                                    <button
                                        v-for="color in colors"
                                        :key="color"
                                        type="button"
                                        @click="form.color = color"
                                        class="size-8 rounded-full transition-all"
                                        :class="form.color === color ? 'ring-2 ring-offset-2 ring-primary scale-110 dark:ring-offset-gray-800' : ''"
                                        :style="{ backgroundColor: color }"
                                    ></button>
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="w-full rounded-xl bg-primary py-3 text-sm font-extrabold text-white shadow-md transition-all active:scale-[0.98]"
                            >
                                {{ editingId ? 'Simpan Perubahan' : 'Tambah Target' }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Deposit Modal -->
        <Teleport to="body">
            <Transition name="slide-up">
                <div
                    v-if="showDeposit"
                    class="fixed inset-0 z-[100] bg-black/40"
                    @click="showDeposit = false"
                >
                    <div @click.stop class="fixed inset-x-0 bottom-0 z-[101] mx-auto max-w-lg rounded-t-3xl bg-white p-5 pb-8 shadow-2xl dark:bg-gray-800">
                        <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">Tabung</h2>
                            <button @click="showDeposit = false" class="rounded-full p-1 active:scale-90">
                                <X class="size-5 text-muted" />
                            </button>
                        </div>

                        <form @submit.prevent="handleDeposit" class="space-y-4">
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Jumlah</label>
                                <input
                                    v-model="depositAmount"
                                    type="number"
                                    min="1"
                                    placeholder="0"
                                    class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    required
                                    autofocus
                                />
                            </div>
                            <button
                                type="submit"
                                class="w-full rounded-xl bg-success py-3 text-sm font-extrabold text-white shadow-md transition-all active:scale-[0.98]"
                            >
                                Tabung Sekarang
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
