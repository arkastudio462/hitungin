<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import api from '@/composables/useApi';
import { User, Mail, Lock, Eye, EyeOff, Loader2, ArrowLeft } from '@lucide/vue';

const router = useRouter();
const auth = useAuthStore();

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const errors = ref({});
const loading = ref(false);
const saving = ref(false);
const showPassword = ref(false);
const showConfirmation = ref(false);
const successMessage = ref('');

onMounted(async () => {
    loading.value = true;
    try {
        await auth.fetchUser();
        form.value.name = auth.user?.name || '';
        form.value.email = auth.user?.email || '';
    } finally {
        loading.value = false;
    }
});

function getFieldError(field) {
    return errors.value[field]?.[0] || null;
}

function hasError(field) {
    return !!errors.value[field];
}

async function handleSubmit() {
    saving.value = true;
    errors.value = {};
    successMessage.value = '';
    try {
        const data = {};
        if (form.value.name) data.name = form.value.name;
        if (form.value.email) data.email = form.value.email;
        if (form.value.password) {
            data.password = form.value.password;
            data.password_confirmation = form.value.password_confirmation;
        }

        const res = await api.put('/user', data);
        auth.user = res.data;
        successMessage.value = 'Profil berhasil diperbarui.';
        form.value.password = '';
        form.value.password_confirmation = '';
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors || {};
        } else {
            errors.value = { name: ['Terjadi kesalahan. Coba lagi.'] };
        }
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <button
                @click="router.back()"
                class="rounded-full p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700 active:scale-95"
            >
                <ArrowLeft class="size-5 text-gray-900 dark:text-white" />
            </button>
            <h1 class="text-xl font-extrabold text-gray-900 dark:text-white">Edit Profil</h1>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="size-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
        </div>

        <template v-else>
            <!-- Avatar -->
            <div class="flex justify-center">
                <div class="flex size-20 items-center justify-center rounded-full bg-primary text-2xl font-bold text-white shadow-lg">
                    {{ auth.user?.name?.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2) || '?' }}
                </div>
            </div>

            <!-- Success Message -->
            <p v-if="successMessage" class="rounded-xl bg-green-50 px-4 py-2.5 text-xs font-medium text-green-600 dark:bg-green-900/20 dark:text-green-400">
                {{ successMessage }}
            </p>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="space-y-4 rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800">
                <!-- Name -->
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                    <div
                        class="flex items-center gap-3 rounded-xl border-2 px-3 transition-all duration-200"
                        :class="hasError('name') ? 'border-red-300 bg-red-50/50 dark:bg-red-900/20' : 'border-border bg-white dark:border-gray-600 dark:bg-gray-700'"
                    >
                        <User class="size-4 shrink-0 text-gray-400 dark:text-gray-500" />
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Nama lengkap"
                            class="w-full border-0 bg-transparent py-2.5 text-sm font-medium text-gray-900 outline-none placeholder:text-gray-400 dark:text-white dark:placeholder:text-gray-500"
                            required
                        />
                    </div>
                    <p v-if="getFieldError('name')" class="mt-1 text-xs font-medium text-red-500">
                        {{ getFieldError('name') }}
                    </p>
                </div>

                <!-- Email -->
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Email</label>
                    <div
                        class="flex items-center gap-3 rounded-xl border-2 px-3 transition-all duration-200"
                        :class="hasError('email') ? 'border-red-300 bg-red-50/50 dark:bg-red-900/20' : 'border-border bg-white dark:border-gray-600 dark:bg-gray-700'"
                    >
                        <Mail class="size-4 shrink-0 text-gray-400 dark:text-gray-500" />
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="nama@email.com"
                            class="w-full border-0 bg-transparent py-2.5 text-sm font-medium text-gray-900 outline-none placeholder:text-gray-400 dark:text-white dark:placeholder:text-gray-500"
                            required
                        />
                    </div>
                    <p v-if="getFieldError('email')" class="mt-1 text-xs font-medium text-red-500">
                        {{ getFieldError('email') }}
                    </p>
                </div>

                <!-- Password -->
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Password Baru <span class="text-muted font-normal">(opsional)</span></label>
                    <div
                        class="flex items-center gap-3 rounded-xl border-2 px-3 transition-all duration-200"
                        :class="hasError('password') ? 'border-red-300 bg-red-50/50 dark:bg-red-900/20' : 'border-border bg-white dark:border-gray-600 dark:bg-gray-700'"
                    >
                        <Lock class="size-4 shrink-0 text-gray-400 dark:text-gray-500" />
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Kosongkan jika tidak ingin mengubah"
                            class="w-full border-0 bg-transparent py-2.5 text-sm font-medium text-gray-900 outline-none placeholder:text-gray-400 dark:text-white dark:placeholder:text-gray-500"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="shrink-0 rounded-lg p-1 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-600 dark:hover:text-gray-300"
                        >
                            <EyeOff v-if="showPassword" class="size-4" />
                            <Eye v-else class="size-4" />
                        </button>
                    </div>
                    <p v-if="getFieldError('password')" class="mt-1 text-xs font-medium text-red-500">
                        {{ getFieldError('password') }}
                    </p>
                </div>

                <!-- Password Confirmation -->
                <div v-if="form.password">
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Konfirmasi Password</label>
                    <div
                        class="flex items-center gap-3 rounded-xl border-2 px-3 transition-all duration-200"
                        :class="form.password && form.password !== form.password_confirmation ? 'border-red-300 bg-red-50/50 dark:bg-red-900/20' : 'border-border bg-white dark:border-gray-600 dark:bg-gray-700'"
                    >
                        <Lock class="size-4 shrink-0 text-gray-400 dark:text-gray-500" />
                        <input
                            v-model="form.password_confirmation"
                            :type="showConfirmation ? 'text' : 'password'"
                            placeholder="Ulangi password baru"
                            class="w-full border-0 bg-transparent py-2.5 text-sm font-medium text-gray-900 outline-none placeholder:text-gray-400 dark:text-white dark:placeholder:text-gray-500"
                        />
                        <button
                            type="button"
                            @click="showConfirmation = !showConfirmation"
                            class="shrink-0 rounded-lg p-1 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-600 dark:hover:text-gray-300"
                        >
                            <EyeOff v-if="showConfirmation" class="size-4" />
                            <Eye v-else class="size-4" />
                        </button>
                    </div>
                    <p
                        v-if="form.password && form.password_confirmation && form.password !== form.password_confirmation"
                        class="mt-1 text-xs font-medium text-red-500"
                    >
                        Password tidak cocok
                    </p>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    :disabled="saving"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary py-3 text-sm font-extrabold text-white shadow-md transition-all active:scale-[0.98] disabled:opacity-50"
                >
                    <Loader2 v-if="saving" class="size-4 animate-spin" />
                    <span v-else>Simpan Perubahan</span>
                </button>
            </form>
        </template>
    </div>
</template>
