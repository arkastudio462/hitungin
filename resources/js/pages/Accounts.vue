<script setup>
import { ref, onMounted } from 'vue';
import { useAccountStore } from '@/stores/accounts';
import { formatCurrency } from '@/lib/utils';
import { Plus, X, Wallet } from '@lucide/vue';

const store = useAccountStore();
const showForm = ref(false);
const editingId = ref(null);
const formError = ref('');

const accountTypes = [
    { value: 'cash', label: 'Tunai', icon: '💵' },
    { value: 'bank', label: 'Bank', icon: '🏦' },
    { value: 'e-wallet', label: 'E-Wallet', icon: '📱' },
    { value: 'credit', label: 'Kartu Kredit', icon: '💳' },
];

const colors = ['#2563eb', '#16a34a', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316', '#64748b'];

const form = ref({
    name: '',
    type: 'cash',
    balance: '',
    icon: '💵',
    color: '#2563eb',
});

onMounted(() => {
    store.fetchAll();
});

function openForm() {
    editingId.value = null;
    formError.value = '';
    form.value = { name: '', type: 'cash', balance: '', icon: '💵', color: '#2563eb' };
    showForm.value = true;
}

function openEdit(a) {
    editingId.value = a.id;
    formError.value = '';
    form.value = {
        name: a.name,
        type: a.type,
        balance: a.balance,
        icon: a.icon || '💵',
        color: a.color || '#2563eb',
    };
    showForm.value = true;
}

function getTypeIcon(type) {
    return accountTypes.find((t) => t.value === type)?.icon || '💰';
}

function getTypeLabel(type) {
    return accountTypes.find((t) => t.value === type)?.label || type;
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
    } catch (e) {
        formError.value = e.response?.data?.message || 'Terjadi kesalahan. Coba lagi.';
    }
}

async function handleDelete(id) {
    if (confirm('Hapus akun ini?')) {
        try {
            await store.remove(id);
        } catch (e) {
            alert(e.response?.data?.message || 'Gagal menghapus akun.');
        }
    }
}
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-extrabold text-gray-900 dark:text-white">Akun</h1>
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
        <div v-else-if="!store.accounts.length" class="rounded-2xl bg-white p-8 text-center shadow-sm dark:bg-gray-800">
            <Wallet class="mx-auto size-10 text-gray-300" />
            <p class="mt-2 text-sm font-bold text-gray-900 dark:text-white">Belum ada akun</p>
            <p class="mt-1 text-xs text-muted">Tambahkan akun untuk melacak saldo</p>
        </div>

        <!-- Account Cards -->
        <div v-else class="space-y-3">
            <!-- Total Balance -->
            <div class="rounded-2xl bg-gradient-to-br from-primary to-primary-dark p-4 text-white shadow-lg">
                <p class="text-xs font-medium opacity-80">Total Saldo</p>
                <p class="mt-1 text-2xl font-extrabold">
                    {{ formatCurrency(store.accounts.reduce((sum, a) => sum + Number(a.balance), 0)) }}
                </p>
            </div>

            <!-- Account List -->
            <div
                v-for="a in store.accounts"
                :key="a.id"
                class="rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800"
            >
                <div class="flex items-center gap-3">
                    <span
                        class="flex size-11 shrink-0 items-center justify-center rounded-xl text-base"
                        :style="{
                            backgroundColor: (a.color || '#e2e8f0') + '20',
                            color: a.color || '#64748b',
                        }"
                    >
                        {{ a.icon || getTypeIcon(a.type) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ a.name }}</p>
                        <p class="text-[11px] text-muted">{{ getTypeLabel(a.type) }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-sm font-extrabold text-gray-900 dark:text-white">{{ formatCurrency(a.balance) }}</p>
                    </div>
                </div>
                <div class="mt-3 flex justify-end gap-2">
                    <button
                        @click="openEdit(a)"
                        class="rounded-full px-3 py-1 text-[10px] font-bold text-primary bg-primary/10 active:scale-95"
                    >
                        Edit
                    </button>
                    <button
                        @click="handleDelete(a.id)"
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
                                {{ editingId ? 'Edit' : 'Tambah' }} Akun
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
                                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Nama Akun</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Contoh: BCA, Dana, Tunai"
                                    class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    required
                                />
                            </div>

                            <!-- Type -->
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Jenis</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        v-for="t in accountTypes"
                                        :key="t.value"
                                        type="button"
                                        @click="form.type = t.value; form.icon = t.icon"
                                        class="flex items-center gap-2 rounded-xl py-2.5 px-3 text-sm font-bold transition-all"
                                        :class="form.type === t.value ? 'bg-primary text-white' : 'bg-gray-100 text-muted dark:bg-gray-700 dark:text-gray-400'"
                                    >
                                        <span>{{ t.icon }}</span>
                                        {{ t.label }}
                                    </button>
                                </div>
                            </div>

                            <!-- Balance -->
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Saldo Saat Ini</label>
                                <input
                                    v-model="form.balance"
                                    type="number"
                                    placeholder="0"
                                    class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    required
                                />
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
                                {{ editingId ? 'Simpan Perubahan' : 'Tambah Akun' }}
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
