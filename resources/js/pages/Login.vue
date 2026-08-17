<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { Mail, Lock, Eye, EyeOff, Loader2 } from '@lucide/vue';

const router = useRouter();
const auth = useAuthStore();

const form = ref({
    email: '',
    password: '',
});

const errors = ref({});
const loading = ref(false);
const showPassword = ref(false);

const isValid = computed(() => form.value.email && form.value.password);

async function handleSubmit() {
    if (!isValid.value) return;
    loading.value = true;
    errors.value = {};
    try {
        await auth.login(form.value);
        router.push('/');
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors || {};
        } else if (e.response?.status === 401) {
            errors.value = { email: ['Email atau password salah.'] };
        } else {
            errors.value = { email: ['Terjadi kesalahan. Coba lagi.'] };
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="flex min-h-screen flex-col overflow-y-auto bg-gradient-to-b from-blue-50 to-white dark:from-gray-900 dark:to-gray-800">
        <!-- Top: Logo & Heading -->
        <div class="m-auto flex w-full max-w-sm flex-col items-center px-5 py-10">
            <!-- Logo Circle -->
            <div class="mb-6 flex size-20 items-center justify-center rounded-3xl bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/25">
                <span class="text-4xl font-extrabold text-white">H</span>
            </div>

            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">Selamat Datang</h1>
            <p class="mt-2 text-center text-sm text-gray-500 dark:text-gray-400">Masuk untuk mengelola keuangan Anda</p>

            <!-- Form Card -->
            <form @submit.prevent="handleSubmit" class="mt-8 w-full max-w-sm space-y-4">
                <!-- Email -->
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300">Email</label>
                    <div
                        class="flex items-center gap-3 rounded-2xl border-2 bg-white px-4 transition-all duration-200 dark:bg-gray-800"
                        :class="errors.email ? 'border-red-300 bg-red-50/50 dark:bg-red-900/20' : 'border-gray-200 focus-within:border-blue-500 focus-within:shadow-lg focus-within:shadow-blue-500/10 dark:border-gray-600 dark:focus-within:border-blue-400'"
                    >
                        <Mail class="size-5 shrink-0 text-gray-400 dark:text-gray-500" />
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="nama@email.com"
                            class="w-full border-0 bg-transparent py-3.5 text-sm font-medium text-gray-900 outline-none placeholder:text-gray-400 dark:text-white dark:placeholder:text-gray-500"
                            required
                        />
                    </div>
                    <p v-if="errors.email" class="mt-1.5 flex items-center gap-1 text-xs font-medium text-red-500">
                        <span class="inline-block size-1 rounded-full bg-red-500"></span>
                        {{ errors.email[0] }}
                    </p>
                </div>

                <!-- Password -->
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300">Password</label>
                    <div
                        class="flex items-center gap-3 rounded-2xl border-2 bg-white px-4 transition-all duration-200 dark:bg-gray-800"
                        :class="errors.password ? 'border-red-300 bg-red-50/50 dark:bg-red-900/20' : 'border-gray-200 focus-within:border-blue-500 focus-within:shadow-lg focus-within:shadow-blue-500/10 dark:border-gray-600 dark:focus-within:border-blue-400'"
                    >
                        <Lock class="size-5 shrink-0 text-gray-400 dark:text-gray-500" />
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Masukkan password"
                            class="w-full border-0 bg-transparent py-3.5 text-sm font-medium text-gray-900 outline-none placeholder:text-gray-400 dark:text-white dark:placeholder:text-gray-500"
                            required
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="shrink-0 rounded-lg p-1 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                        >
                            <EyeOff v-if="showPassword" class="size-5" />
                            <Eye v-else class="size-5" />
                        </button>
                    </div>
                    <p v-if="errors.password" class="mt-1.5 flex items-center gap-1 text-xs font-medium text-red-500">
                        <span class="inline-block size-1 rounded-full bg-red-500"></span>
                        {{ errors.password[0] }}
                    </p>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    :disabled="loading || !isValid"
                    class="flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-500 to-blue-600 py-4 text-sm font-bold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:from-blue-600 hover:to-blue-700 hover:shadow-xl hover:shadow-blue-500/30 active:scale-[0.98] disabled:opacity-50 disabled:shadow-none"
                >
                    <Loader2 v-if="loading" class="size-5 animate-spin" />
                    <span v-else>Masuk</span>
                </button>
            </form>
        </div>

        <!-- Bottom: Register Link -->
        <div class="shrink-0 px-5 pb-8 pt-4 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Belum punya akun?
                <RouterLink to="/register" class="ml-1 font-bold text-blue-600 transition-colors hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                    Daftar sekarang
                </RouterLink>
            </p>
        </div>
    </div>
</template>
