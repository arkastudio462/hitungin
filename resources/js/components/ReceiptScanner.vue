<script setup>
import { ref, watch, onMounted } from 'vue';
import { useReceiptStore } from '@/stores/receipts';
import { useCategoryStore } from '@/stores/categories';
import { useAccountStore } from '@/stores/accounts';
import { X, Camera, Upload, Loader2, Check, ImageIcon } from '@lucide/vue';

const emit = defineEmits(['close', 'saved']);

const receiptStore = useReceiptStore();
const categoryStore = useCategoryStore();
const accountStore = useAccountStore();

const fileInput = ref(null);
const cameraInput = ref(null);
const preview = ref(null);
const selectedFile = ref(null);
const step = ref('upload');
const categories = ref([]);

const form = ref({
    category_id: '',
    account_id: '',
    type: 'expense',
    amount: '',
    description: '',
    date: new Date().toISOString().split('T')[0],
});

watch(() => form.value.type, async (type) => {
    await categoryStore.fetchAll(type);
    categories.value = categoryStore.categories;
}, { immediate: true });

onMounted(() => {
    accountStore.fetchAll();
});

function onFileSelected(e) {
    const file = e.target.files?.[0];
    if (!file) return;
    processFile(file);
}

function processFile(file) {
    selectedFile.value = file;
    preview.value = URL.createObjectURL(file);
    step.value = 'scanning';
    receiptStore.scan(file).then((data) => {
        const parsed = data.parsed;
        form.value.type = parsed.type || 'expense';
        form.value.amount = parsed.total != null ? parsed.total : '';
        form.value.date = parsed.date || new Date().toISOString().split('T')[0];
        form.value.description = parsed.description || '';
        step.value = 'review';
    }).catch(() => {
        step.value = 'upload';
        preview.value = null;
        selectedFile.value = null;
    });
}

function triggerCamera() {
    cameraInput.value?.click();
}

function triggerUpload() {
    fileInput.value?.click();
}

function removeImage() {
    preview.value = null;
    selectedFile.value = null;
    receiptStore.reset();
    step.value = 'upload';
}

async function handleSave() {
    try {
        await receiptStore.save({
            category_id: form.value.category_id,
            account_id: form.value.account_id || undefined,
            type: form.value.type,
            amount: form.value.amount,
            description: form.value.description,
            date: form.value.date,
        });
        emit('saved');
        emit('close');
    } catch (e) {
        // error handled by store
    }
}

function close() {
    receiptStore.reset();
    emit('close');
}
</script>

<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-[60] flex items-end justify-center bg-black/40 sm:items-center" @click.self="close">
            <div class="w-full max-w-md rounded-t-3xl bg-white p-5 pb-8 animate-slide-up sm:rounded-3xl dark:bg-gray-800">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">Scan Struk</h2>
                    <button @click="close" class="rounded-full p-1 active:scale-90">
                        <X class="size-5 text-muted" />
                    </button>
                </div>

                <!-- Error -->
                <p v-if="receiptStore.error" class="mb-4 rounded-xl bg-red-50 px-4 py-2.5 text-xs font-medium text-red-600 dark:bg-red-900/20 dark:text-red-400">
                    {{ receiptStore.error }}
                </p>

                <!-- Step: Upload -->
                <div v-if="step === 'upload'" class="space-y-4">
                    <input
                        ref="cameraInput"
                        type="file"
                        accept="image/*"
                        capture="environment"
                        class="hidden"
                        @change="onFileSelected"
                    />
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="onFileSelected"
                    />

                    <div
                        class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 py-10 dark:border-gray-600 dark:bg-gray-700/50"
                    >
                        <ImageIcon class="size-12 text-gray-300 dark:text-gray-500" />
                        <p class="mt-2 text-sm font-bold text-gray-900 dark:text-white">Unggah Struk</p>
                        <p class="mt-1 text-xs text-muted">Foto atau pilih dari galeri</p>

                        <div class="mt-4 flex gap-3">
                            <button
                                @click="triggerCamera"
                                class="flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-md transition-all active:scale-95"
                            >
                                <Camera class="size-4" />
                                Kamera
                            </button>
                            <button
                                @click="triggerUpload"
                                class="flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-gray-900 border-2 border-border shadow-sm transition-all active:scale-95 dark:bg-gray-700 dark:text-white dark:border-gray-600"
                            >
                                <Upload class="size-4" />
                                Galeri
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step: Scanning -->
                <div v-else-if="step === 'scanning'" class="space-y-4">
                    <div v-if="preview" class="overflow-hidden rounded-2xl">
                        <img :src="preview" class="w-full object-contain max-h-48" />
                    </div>
                    <div class="flex flex-col items-center py-6">
                        <Loader2 class="size-10 animate-spin text-primary" />
                        <p class="mt-3 text-sm font-bold text-gray-900 dark:text-white">Menganalisa struk...</p>
                        <p class="mt-1 text-xs text-muted">Menggunakan AI untuk membaca isi struk</p>
                    </div>
                </div>

                <!-- Step: Review -->
                <div v-else-if="step === 'review'" class="space-y-4">
                    <!-- Preview -->
                    <div v-if="preview" class="relative overflow-hidden rounded-2xl">
                        <img :src="preview" class="w-full object-contain max-h-36" />
                        <button
                            @click="removeImage"
                            class="absolute right-2 top-2 rounded-full bg-black/50 p-1.5 text-white active:scale-90"
                        >
                            <X class="size-3.5" />
                        </button>
                    </div>

                    <form @submit.prevent="handleSave" class="space-y-3">
                        <!-- Type -->
                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="form.type = 'expense'"
                                class="flex-1 rounded-xl py-2.5 text-sm font-bold transition-all"
                                :class="form.type === 'expense' ? 'bg-danger text-white' : 'bg-gray-100 text-muted dark:bg-gray-700'"
                            >
                                Pengeluaran
                            </button>
                            <button
                                type="button"
                                @click="form.type = 'income'"
                                class="flex-1 rounded-xl py-2.5 text-sm font-bold transition-all"
                                :class="form.type === 'income' ? 'bg-success text-white' : 'bg-gray-100 text-muted dark:bg-gray-700'"
                            >
                                Pemasukan
                            </button>
                        </div>

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

                        <!-- Account -->
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Akun</label>
                            <select
                                v-model="form.account_id"
                                class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:bg-gray-700 dark:border-gray-600 dark:text-white"
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
                                class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                required
                            />
                        </div>

                        <!-- Date -->
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Tanggal</label>
                            <input
                                v-model="form.date"
                                type="date"
                                class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                required
                            />
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Catatan</label>
                            <input
                                v-model="form.description"
                                type="text"
                                placeholder="Opsional"
                                class="w-full rounded-xl border-2 border-border bg-white px-3 py-2.5 text-sm font-medium dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="receiptStore.loading"
                            class="w-full flex items-center justify-center gap-2 rounded-xl bg-primary py-3 text-sm font-extrabold text-white shadow-md transition-all active:scale-[0.98] disabled:opacity-50"
                        >
                            <Loader2 v-if="receiptStore.loading" class="size-4 animate-spin" />
                            <Check v-else class="size-4" />
                            {{ receiptStore.loading ? 'Menyimpan...' : 'Simpan Transaksi' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </Teleport>
</template>
