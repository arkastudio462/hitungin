<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import api from '@/composables/useApi';
import { formatCurrency, formatDate, formatMonth } from '@/lib/utils';
import { TrendingUp, TrendingDown, Wallet, ArrowRight, CreditCard } from '@lucide/vue';
import { useRouter } from 'vue-router';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const router = useRouter();
const data = ref({
    total_income: 0,
    total_expense: 0,
    balance: 0,
    total_balance: 0,
    recent_transactions: [],
    category_breakdown: [],
});
const trendData = ref([]);
const loading = ref(true);
const period = ref('month');

const doughnutRef = ref(null);
const barRef = ref(null);
let doughnutChart = null;
let barChart = null;

const periods = [
    { value: 'week', label: 'Minggu' },
    { value: 'month', label: 'Bulan' },
    { value: 'year', label: 'Tahun' },
];

async function fetchData() {
    loading.value = true;
    try {
        const [dashRes, trendRes] = await Promise.all([
            api.get('/dashboard', { params: { period: period.value } }),
            api.get('/reports/trend', { params: { months: 6 } }),
        ]);
        data.value = dashRes.data;
        trendData.value = trendRes.data.trend;
    } finally {
        loading.value = false;
    }
}

function renderDoughnut() {
    if (doughnutChart) {
        doughnutChart.destroy();
        doughnutChart = null;
    }
    if (!doughnutRef.value) return;

    const income = Number(data.value.total_income);
    const expense = Number(data.value.total_expense);

    doughnutChart = new Chart(doughnutRef.value, {
        type: 'doughnut',
        data: {
            labels: ['Masuk', 'Keluar'],
            datasets: [{
                data: [income || 1, expense || 0],
                backgroundColor: ['#22c55e', '#ef4444'],
                borderWidth: 0,
                borderRadius: 6,
                spacing: 3,
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { size: 11, weight: '600' },
                    bodyFont: { size: 11 },
                    padding: { top: 8, bottom: 8, left: 12, right: 12 },
                    cornerRadius: 10,
                    displayColors: true,
                    boxWidth: 8,
                    boxHeight: 8,
                    boxPadding: 4,
                    callbacks: {
                        label: (ctx) => ` ${ctx.dataset.label}: ${formatCurrency(ctx.raw)}`,
                    },
                },
            },
        },
        plugins: [{
            id: 'centerText',
            beforeDraw(chart) {
                const { ctx, width, height } = chart;
                const total = income + expense;
                const incomePct = total > 0 ? Math.round((income / total) * 100) : 0;

                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                ctx.font = 'bold 18px "Instrument Sans", sans-serif';
                ctx.fillStyle = '#1e293b';
                ctx.fillText(`${incomePct}%`, width / 2, height / 2 - 6);

                ctx.font = '500 10px "Instrument Sans", sans-serif';
                ctx.fillStyle = '#94a3b8';
                ctx.fillText('Pemasukan', width / 2, height / 2 + 14);

                ctx.restore();
            },
        }],
    });
}

function renderBar() {
    if (barChart) {
        barChart.destroy();
        barChart = null;
    }
    if (!barRef.value || !trendData.value.length) return;

    const labels = trendData.value.map((t) => formatMonth(parseInt(t.month.split('-')[1])));
    const incomeData = trendData.value.map((t) => Number(t.income));
    const expenseData = trendData.value.map((t) => Number(t.expense));

    barChart = new Chart(barRef.value, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Masuk',
                    data: incomeData,
                    backgroundColor: 'rgba(34, 197, 94, 0.75)',
                    hoverBackgroundColor: '#22c55e',
                    borderRadius: 6,
                    borderSkipped: false,
                    barPercentage: 0.5,
                    categoryPercentage: 0.65,
                },
                {
                    label: 'Keluar',
                    data: expenseData,
                    backgroundColor: 'rgba(239, 68, 68, 0.75)',
                    hoverBackgroundColor: '#ef4444',
                    borderRadius: 6,
                    borderSkipped: false,
                    barPercentage: 0.5,
                    categoryPercentage: 0.65,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { size: 11, weight: '600' },
                    bodyFont: { size: 11 },
                    padding: { top: 8, bottom: 8, left: 12, right: 12 },
                    cornerRadius: 10,
                    displayColors: true,
                    boxWidth: 8,
                    boxHeight: 8,
                    boxPadding: 4,
                    callbacks: {
                        label: (ctx) => ` ${ctx.dataset.label}: ${formatCurrency(ctx.raw)}`,
                    },
                },
            },
            scales: {
                y: {
                    display: false,
                    beginAtZero: true,
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10, weight: '500' }, color: '#94a3b8', padding: 4 },
                    border: { display: false },
                },
            },
        },
    });
}

watch(loading, async (val) => {
    if (!val) {
        await nextTick();
        await nextTick();
        renderDoughnut();
        renderBar();
    }
});

watch(period, () => {
    fetchData();
});

onMounted(() => {
    fetchData();
});

onBeforeUnmount(() => {
    if (doughnutChart) { doughnutChart.destroy(); doughnutChart = null; }
    if (barChart) { barChart.destroy(); barChart = null; }
});
</script>

<template>
    <div class="space-y-4">
        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-20">
            <div class="size-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
        </div>

        <template v-else>
            <!-- Balance Card -->
            <div class="rounded-2xl bg-gradient-to-br from-primary to-primary-dark p-5 text-white shadow-lg overflow-hidden">
                <div class="flex items-center justify-between">
                    <div class="min-w-0">
                        <p class="text-sm font-medium opacity-80">Saldo {{ periods.find(p => p.value === period)?.label }}</p>
                        <p class="mt-1 text-2xl font-extrabold sm:text-3xl truncate">{{ formatCurrency(data.balance) }}</p>
                    </div>
                </div>

                <!-- Period Selector -->
                <div class="mt-3 flex gap-1 rounded-full bg-white/15 p-0.5">
                    <button
                        v-for="p in periods"
                        :key="p.value"
                        @click="period = p.value"
                        class="flex-1 rounded-full py-1.5 text-[10px] font-bold transition-all"
                        :class="period === p.value ? 'bg-white text-primary shadow-sm' : 'text-white/70'"
                    >
                        {{ p.label }}
                    </button>
                </div>

                <div class="mt-4 flex gap-4">
                    <div class="flex items-center gap-2">
                        <div class="flex size-8 items-center justify-center rounded-full bg-white/20">
                            <TrendingUp class="size-4" />
                        </div>
                        <div>
                            <p class="text-[10px] font-medium opacity-70">Masuk</p>
                            <p class="text-sm font-bold">{{ formatCurrency(data.total_income) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex size-8 items-center justify-center rounded-full bg-white/20">
                            <TrendingDown class="size-4" />
                        </div>
                        <div>
                            <p class="text-[10px] font-medium opacity-70">Keluar</p>
                            <p class="text-sm font-bold">{{ formatCurrency(data.total_expense) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Balance -->
            <div class="rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-primary/10">
                        <CreditCard class="size-5 text-primary" />
                    </div>
                    <div>
                        <p class="text-xs text-muted">Total Saldo Semua Akun</p>
                        <p class="text-lg font-extrabold text-gray-900 dark:text-white">{{ formatCurrency(data.total_balance) }}</p>
                    </div>
                </div>
            </div>

            <!-- Doughnut + Bar Row -->
            <div class="grid grid-cols-2 gap-3">
                <!-- Doughnut Chart -->
                <div class="rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800 overflow-hidden">
                    <h3 class="mb-1 text-xs font-bold text-gray-900 dark:text-white">Rasio</h3>
                    <div class="relative h-36 overflow-hidden">
                        <canvas ref="doughnutRef"></canvas>
                    </div>
                    <div class="mt-2 flex justify-center gap-4">
                        <span class="flex items-center gap-1 text-[10px] font-semibold text-success">
                            <span class="size-1.5 rounded-full bg-success"></span> Masuk
                        </span>
                        <span class="flex items-center gap-1 text-[10px] font-semibold text-danger">
                            <span class="size-1.5 rounded-full bg-danger"></span> Keluar
                        </span>
                    </div>
                </div>

                <!-- Bar Chart -->
                <div class="rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800 overflow-hidden">
                    <h3 class="mb-1 text-xs font-bold text-gray-900 dark:text-white">Trend 6 Bulan</h3>
                    <div class="h-36 overflow-hidden">
                        <canvas ref="barRef"></canvas>
                    </div>
                    <div class="mt-2 flex justify-center gap-4">
                        <span class="flex items-center gap-1 text-[10px] font-semibold text-success">
                            <span class="size-1.5 rounded-full bg-success"></span> Masuk
                        </span>
                        <span class="flex items-center gap-1 text-[10px] font-semibold text-danger">
                            <span class="size-1.5 rounded-full bg-danger"></span> Keluar
                        </span>
                    </div>
                </div>
            </div>

            <!-- Category Breakdown -->
            <div v-if="data.category_breakdown.length" class="rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800">
                <h3 class="mb-3 text-sm font-bold text-gray-900 dark:text-white">Pengeluaran per Kategori</h3>
                <div class="space-y-3">
                    <div
                        v-for="cat in data.category_breakdown"
                        :key="cat.name"
                        class="flex items-center gap-3"
                    >
                        <span
                            class="flex size-9 shrink-0 items-center justify-center rounded-xl text-sm"
                            :style="{ backgroundColor: (cat.color || '#e2e8f0') + '20', color: cat.color || '#64748b' }"
                        >
                            {{ cat.icon || '📦' }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ cat.name }}</p>
                            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :style="{
                                        width: `${data.category_breakdown[0].total > 0 ? (cat.total / data.category_breakdown[0].total) * 100 : 0}%`,
                                        backgroundColor: cat.color || '#2563eb',
                                    }"
                                ></div>
                            </div>
                        </div>
                        <p class="shrink-0 text-sm font-bold text-gray-900 dark:text-white">{{ formatCurrency(cat.total) }}</p>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Transaksi Terakhir</h3>
                    <button
                        @click="router.push('/transactions')"
                        class="flex items-center gap-1 text-xs font-semibold text-primary active:scale-95"
                    >
                        Lihat semua
                        <ArrowRight class="size-3" />
                    </button>
                </div>

                <div v-if="!data.recent_transactions.length" class="py-8 text-center">
                    <Wallet class="mx-auto size-10 text-gray-300" />
                    <p class="mt-2 text-sm text-muted">Belum ada transaksi</p>
                </div>

                <div v-else class="space-y-2">
                    <div
                        v-for="t in data.recent_transactions"
                        :key="t.id"
                        class="flex items-center gap-3 rounded-xl p-2 transition-colors active:bg-gray-50 dark:active:bg-gray-700"
                    >
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl text-base"
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
                        </div>
                        <p
                            class="shrink-0 text-sm font-extrabold"
                            :class="t.type === 'income' ? 'text-success' : 'text-danger'"
                        >
                            {{ t.type === 'income' ? '+' : '-' }}{{ formatCurrency(t.amount) }}
                        </p>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
