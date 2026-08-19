<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { Bell, BarChart3, Shield, ChevronRight, Wallet } from '@lucide/vue';

const router = useRouter();
const currentSlide = ref(0);

const slides = [
    {
        icon: Bell,
        title: 'Otomatis dari Notifikasi',
        desc: 'Hitungin membaca notifikasi bank dan e-wallet lalu mencatat transaksi secara otomatis.',
        color: 'from-blue-500 to-blue-600',
        bg: 'bg-blue-50 dark:bg-blue-900/20',
    },
    {
        icon: BarChart3,
        title: 'Dashboard Visual',
        desc: 'Lihat grafik pemasukan dan pengeluaran secara real-time. Mudah dipahami.',
        color: 'from-emerald-500 to-emerald-600',
        bg: 'bg-emerald-50 dark:bg-emerald-900/20',
    },
    {
        icon: Shield,
        title: 'Aman & Privat',
        desc: 'Data keuangan kamu tersimpan aman. Tidak ada data yang dibagikan ke pihak ketiga.',
        color: 'from-violet-500 to-violet-600',
        bg: 'bg-violet-50 dark:bg-violet-900/20',
    },
];

const isLast = computed(() => currentSlide.value === slides.length - 1);

function next() {
    if (isLast.value) {
        localStorage.setItem('hitungin_welcomed', '1');
        router.push('/login');
    } else {
        currentSlide.value++;
    }
}

function skip() {
    localStorage.setItem('hitungin_welcomed', '1');
    router.push('/login');
}
</script>

<template>
    <div class="flex full-height flex-col bg-white dark:bg-gray-900">
        <!-- Skip -->
        <div class="flex justify-end px-5 pt-4">
            <button
                v-if="!isLast"
                @click="skip"
                class="text-sm font-semibold text-muted transition-colors hover:text-gray-900 dark:hover:text-white"
            >
                Lewati
            </button>
        </div>

        <!-- Slides -->
        <div class="flex flex-1 flex-col items-center justify-center px-6 py-10">
            <Transition name="fade" mode="out-in">
                <div :key="currentSlide" class="flex flex-col items-center text-center">
                    <!-- Icon -->
                    <div
                        class="mb-8 flex size-24 items-center justify-center rounded-3xl shadow-lg"
                        :class="[slides[currentSlide].bg]"
                    >
                        <div class="flex size-16 items-center justify-center rounded-2xl bg-gradient-to-br" :class="slides[currentSlide].color">
                            <component :is="slides[currentSlide].icon" class="size-8 text-white" />
                        </div>
                    </div>

                    <!-- Text -->
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">
                        {{ slides[currentSlide].title }}
                    </h1>
                    <p class="mt-3 max-w-xs text-sm leading-relaxed text-muted">
                        {{ slides[currentSlide].desc }}
                    </p>
                </div>
            </Transition>

            <!-- Dots -->
            <div class="mt-10 flex gap-2">
                <span
                    v-for="(_, i) in slides"
                    :key="i"
                    class="h-2 rounded-full transition-all duration-300"
                    :class="i === currentSlide ? 'w-7 bg-primary' : 'w-2 bg-gray-300 dark:bg-gray-600'"
                ></span>
            </div>
        </div>

        <!-- Bottom Button -->
        <div class="shrink-0 px-6 pb-8 pt-4">
            <button
                @click="next"
                class="flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-500 to-blue-600 py-4 text-sm font-bold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:from-blue-600 hover:to-blue-700 active:scale-[0.98]"
            >
                {{ isLast ? 'Mulai Sekarang' : 'Selanjutnya' }}
                <ChevronRight v-if="!isLast" class="size-5" />
                <Wallet v-else class="size-5" />
            </button>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
