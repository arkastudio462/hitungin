<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick, computed } from 'vue';
import api from '@/composables/useApi';
import { formatCurrency, formatMonth } from '@/lib/utils';
import { BarChart3 } from '@lucide/vue';
import { Chart, registerables } from 'chart.js';
import { useThemeStore } from '@/stores/theme';

Chart.register(...registerables);

const theme = useThemeStore();

const summary = ref({ year: new Date().getFullYear(), monthly: [] });
const trend = ref({ months: 6, trend: [] });
const categoryBreakdown = ref({ categories: [] });
const loading = ref(true);

const trendCanvas = ref(null);
const barCanvas = ref(null);
const categoryCanvas = ref(null);

let trendChart = null;
let barChart = null;
let categoryChart = null;

const selectedType = ref('expense');

const chartColors = computed(() => ({
    grid: theme.isDark ? '#374151' : '#f1f5f9',
    tick: theme.isDark ? '#9ca3af' : '#94a3b8',
    legend: theme.isDark ? '#d1d5db' : '#374151',
    doughnutBorder: theme.isDark ? '#1f2937' : '#ffffff',
    centerIcon: theme.isDark ? '#f3f4f6' : '#1e293b',
    centerText: theme.isDark ? '#9ca3af' : '#64748b',
}));

async function fetchData() {
    try {
        const [summaryRes, trendRes, catRes] = await Promise.all([
            api.get('/reports/summary'),
            api.get('/reports/trend', { params: { months: 6 } }),
            api.get('/reports/by-category', { params: { type: selectedType.value } }),
        ]);
        summary.value = summaryRes.data;
        trend.value = trendRes.data;
        categoryBreakdown.value = catRes.data;
    } finally {
        loading.value = false;
    }
}

async function fetchCategoryData() {
    const res = await api.get('/reports/by-category', { params: { type: selectedType.value } });
    categoryBreakdown.value = res.data;
    await nextTick();
    renderCategoryChart();
}

function renderTrendChart() {
    if (trendChart) { trendChart.destroy(); trendChart = null; }
    if (!trendCanvas.value || !trend.value.trend.length) return;

    const canvas = trendCanvas.value;
    const ctx = canvas.getContext('2d');

    const labels = trend.value.trend.map((t) => formatMonth(parseInt(t.month.split('-')[1])));
    const incomeData = trend.value.trend.map((t) => Number(t.income));
    const expenseData = trend.value.trend.map((t) => Number(t.expense));

    const incomeGrad = ctx.createLinearGradient(0, 0, 0, canvas.height || 224);
    incomeGrad.addColorStop(0, 'rgba(34, 197, 94, 0.25)');
    incomeGrad.addColorStop(1, 'rgba(34, 197, 94, 0.01)');

    const expenseGrad = ctx.createLinearGradient(0, 0, 0, canvas.height || 224);
    expenseGrad.addColorStop(0, 'rgba(239, 68, 68, 0.20)');
    expenseGrad.addColorStop(1, 'rgba(239, 68, 68, 0.01)');

    trendChart = new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Masuk',
                    data: incomeData,
                    borderColor: '#22c55e',
                    backgroundColor: incomeGrad,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#22c55e',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#22c55e',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                },
                {
                    label: 'Keluar',
                    data: expenseData,
                    borderColor: '#ef4444',
                    backgroundColor: expenseGrad,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#ef4444',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#ef4444',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, boxHeight: 10, padding: 16, font: { size: 11, weight: '600' }, usePointStyle: true, pointStyle: 'circle', color: chartColors.value.legend },
                },
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
                    callbacks: { label: (ctx) => ` ${ctx.dataset.label}: ${formatCurrency(ctx.raw)}` },
                },
            },
            scales: {
                y: { beginAtZero: true, grid: { color: chartColors.value.grid }, ticks: { font: { size: 10 }, color: chartColors.value.tick, callback: (v) => v >= 1000000 ? (v / 1000000).toFixed(0) + 'jt' : v >= 1000 ? (v / 1000).toFixed(0) + 'rb' : v } },
                x: { grid: { display: false }, ticks: { font: { size: 10, weight: '500' }, color: chartColors.value.tick }, border: { display: false } },
            },
        },
    });
}

function renderBarChart() {
    if (barChart) { barChart.destroy(); barChart = null; }
    if (!barCanvas.value || !summary.value.monthly.length) return;

    const labels = summary.value.monthly.map((m) => formatMonth(parseInt(m.month)));
    const incomeData = summary.value.monthly.map((m) => Number(m.income));
    const expenseData = summary.value.monthly.map((m) => Number(m.expense));

    barChart = new Chart(barCanvas.value, {
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
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, boxHeight: 10, padding: 16, font: { size: 11, weight: '600' }, usePointStyle: true, pointStyle: 'circle', color: chartColors.value.legend },
                },
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
                    callbacks: { label: (ctx) => ` ${ctx.dataset.label}: ${formatCurrency(ctx.raw)}` },
                },
            },
            scales: {
                y: { beginAtZero: true, grid: { color: chartColors.value.grid }, ticks: { font: { size: 10 }, color: chartColors.value.tick, callback: (v) => v >= 1000000 ? (v / 1000000).toFixed(0) + 'jt' : v >= 1000 ? (v / 1000).toFixed(0) + 'rb' : v } },
                x: { grid: { display: false }, ticks: { font: { size: 10, weight: '500' }, color: chartColors.value.tick }, border: { display: false } },
            },
        },
    });
}

function renderCategoryChart() {
    if (categoryChart) { categoryChart.destroy(); categoryChart = null; }
    if (!categoryCanvas.value || !categoryBreakdown.value.categories.length) return;

    const cats = categoryBreakdown.value.categories;
    const labels = cats.map((c) => c.name);
    const values = cats.map((c) => Number(c.total));
    const colors = cats.map((c) => c.color || '#94a3b8');

    categoryChart = new Chart(categoryCanvas.value, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: chartColors.value.doughnutBorder,
                borderRadius: 4,
                spacing: 2,
                hoverOffset: 8,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, boxHeight: 10, padding: 12, font: { size: 10, weight: '500' }, usePointStyle: true, pointStyle: 'circle', color: chartColors.value.legend },
                },
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
                        label: (ctx) => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = total > 0 ? Math.round((ctx.raw / total) * 100) : 0;
                            return ` ${ctx.label}: ${formatCurrency(ctx.raw)} (${pct}%)`;
                        },
                    },
                },
            },
        },
        plugins: [{
            id: 'centerIcon',
            beforeDraw(chart) {
                const { ctx, width, height } = chart;
                const cats = categoryBreakdown.value.categories;
                if (!cats.length) return;
                const top = cats[0];

                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                ctx.font = '20px sans-serif';
                ctx.fillStyle = chartColors.value.centerIcon;
                ctx.fillText(top.icon || '📦', width / 2, height / 2 - 10);

                ctx.font = 'bold 10px "Instrument Sans", sans-serif';
                ctx.fillStyle = chartColors.value.centerText;
                ctx.fillText(top.name, width / 2, height / 2 + 12);

                ctx.restore();
            },
        }],
    });
}

watch(loading, async (val) => {
    if (!val) {
        await nextTick();
        await nextTick();
        renderTrendChart();
        renderBarChart();
        renderCategoryChart();
    }
});

watch(selectedType, async () => {
    if (!loading.value) {
        await fetchCategoryData();
    }
});

watch(() => theme.isDark, async () => {
    if (!loading.value) {
        await nextTick();
        renderTrendChart();
        renderBarChart();
        renderCategoryChart();
    }
});

onMounted(() => {
    fetchData();
});

onBeforeUnmount(() => {
    if (trendChart) { trendChart.destroy(); trendChart = null; }
    if (barChart) { barChart.destroy(); barChart = null; }
    if (categoryChart) { categoryChart.destroy(); categoryChart = null; }
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <h1 class="text-xl font-extrabold text-gray-900 dark:text-white">Laporan</h1>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="size-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
        </div>

        <template v-else>
            <!-- Trend Chart -->
            <div class="rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800">
                <h3 class="mb-3 text-sm font-bold text-gray-900 dark:text-white">Trend {{ trend.months }} Bulan Terakhir</h3>
                <div class="h-56">
                    <canvas ref="trendCanvas"></canvas>
                </div>
            </div>

            <!-- Bar + Doughnut Row -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <!-- Bar Chart -->
                <div class="rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800">
                    <h3 class="mb-3 text-sm font-bold text-gray-900 dark:text-white">Perbandingan Bulanan</h3>
                    <div class="h-52">
                        <canvas ref="barCanvas"></canvas>
                    </div>
                </div>

                <!-- Category Doughnut -->
                <div class="rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Per Kategori</h3>
                        <div class="flex gap-1 rounded-full bg-gray-100 dark:bg-gray-700 p-0.5">
                            <button
                                @click="selectedType = 'expense'"
                                class="rounded-full px-3 py-1 text-[10px] font-bold transition-all"
                                :class="selectedType === 'expense' ? 'bg-danger text-white shadow-sm' : 'text-muted'"
                            >
                                Keluar
                            </button>
                            <button
                                @click="selectedType = 'income'"
                                class="rounded-full px-3 py-1 text-[10px] font-bold transition-all"
                                :class="selectedType === 'income' ? 'bg-success text-white shadow-sm' : 'text-muted'"
                            >
                                Masuk
                            </button>
                        </div>
                    </div>
                    <div v-if="categoryBreakdown.categories.length" class="h-52">
                        <canvas ref="categoryCanvas"></canvas>
                    </div>
                    <div v-else class="flex h-52 items-center justify-center">
                        <p class="text-xs text-muted">Belum ada data</p>
                    </div>
                </div>
            </div>

            <!-- Year Summary -->
            <div class="rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800">
                <h3 class="mb-3 text-sm font-bold text-gray-900 dark:text-white">Ringkasan {{ summary.year }}</h3>
                <div class="space-y-1">
                    <div
                        v-for="m in summary.monthly"
                        :key="m.month"
                        class="flex items-center justify-between rounded-xl px-3 py-2 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatMonth(parseInt(m.month)) }}</span>
                        <div class="flex gap-4">
                            <span class="text-xs font-bold text-success">+{{ formatCurrency(m.income) }}</span>
                            <span class="text-xs font-bold text-danger">-{{ formatCurrency(m.expense) }}</span>
                        </div>
                    </div>
                    <div v-if="!summary.monthly.length" class="py-4 text-center">
                        <p class="text-xs text-muted">Belum ada data</p>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
