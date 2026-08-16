<script setup>
import { ref, onMounted } from 'vue';
import { useCategoryStore } from '@/stores/categories';
import { Plus, X, Tags } from '@lucide/vue';

const store = useCategoryStore();
const showForm = ref(false);
const editingId = ref(null);
const formError = ref('');

const colors = ['#2563eb', '#dc2626', '#16a34a', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316'];
const icons = ['🍔', '🛒', '🏠', '🚗', '💰', '📱', '🎮', '📚', '✈️', '💊', '🎁', '💼'];

const form = ref({
    name: '',
    type: 'expense',
    icon: '📦',
    color: '#2563eb',
});

onMounted(() => {
    store.fetchAll();
});

function openForm() {
    editingId.value = null;
    formError.value = '';
    form.value = { name: '', type: 'expense', icon: '📦', color: '#2563eb' };
    showForm.value = true;
}

function openEdit(c) {
    editingId.value = c.id;
    formError.value = '';
    form.value = { name: c.name, type: c.type, icon: c.icon || '📦', color: c.color || '#2563eb' };
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
    } catch (e) {
        formError.value = e.response?.data?.message || 'Terjadi kesalahan. Coba lagi.';
    }
}

async function handleDelete(id) {
    if (confirm('Hapus kategori ini?')) {
        try {
            await store.remove(id);
        } catch (e) {
            alert(e.response?.data?.message || 'Gagal menghapus kategori.');
        }
    }
}

function filterByType(type) {
    return store.categories.filter((c) => c.type === type);
}
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-extrabold text-gray-900 dark:text-white">Kategori</h1>
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

        <template v-else>
            <!-- Expense Categories -->
            <div class="rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800">
                <h3 class="mb-3 text-sm font-bold text-gray-900 dark:text-white">Pengeluaran</h3>
                <div v-if="!filterByType('expense').length" class="py-4 text-center">
                    <p class="text-xs text-muted">Belum ada kategori</p>
                </div>
                <div v-else class="space-y-2">
                    <div
                        v-for="c in filterByType('expense')"
                        :key="c.id"
                        class="flex items-center gap-3 rounded-xl p-2 transition-colors active:bg-gray-50 dark:active:bg-gray-700"
                        @click="openEdit(c)"
                    >
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl text-base"
                            :style="{ backgroundColor: c.color + '20' }"
                        >
                            {{ c.icon || '📦' }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ c.name }}</p>
                        </div>
                        <button
                            @click.stop="handleDelete(c.id)"
                            class="text-[10px] font-semibold text-danger/60 active:text-danger"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            </div>

            <!-- Income Categories -->
            <div class="rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800">
                <h3 class="mb-3 text-sm font-bold text-gray-900 dark:text-white">Pemasukan</h3>
                <div v-if="!filterByType('income').length" class="py-4 text-center">
                    <p class="text-xs text-muted">Belum ada kategori</p>
                </div>
                <div v-else class="space-y-2">
                    <div
                        v-for="c in filterByType('income')"
                        :key="c.id"
                        class="flex items-center gap-3 rounded-xl p-2 transition-colors active:bg-gray-50 dark:active:bg-gray-700"
                        @click="openEdit(c)"
                    >
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl text-base"
                            :style="{ backgroundColor: c.color + '20' }"
                        >
                            {{ c.icon || '📦' }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ c.name }}</p>
                        </div>
                        <button
                            @click.stop="handleDelete(c.id)"
                            class="text-[10px] font-semibold text-danger/60 active:text-danger"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </template>

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
                                {{ editingId ? 'Edit' : 'Tambah' }} Kategori
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

                            <!-- Name -->
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Nama</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Nama kategori"
                                    class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    required
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
                                {{ editingId ? 'Simpan Perubahan' : 'Tambah Kategori' }}
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
