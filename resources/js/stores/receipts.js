import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/composables/useApi';

export const useReceiptStore = defineStore('receipts', () => {
    const scanResult = ref(null);
    const receiptPath = ref(null);
    const loading = ref(false);
    const error = ref('');

    async function scan(file) {
        loading.value = true;
        error.value = '';
        scanResult.value = null;
        receiptPath.value = null;

        try {
            const formData = new FormData();
            formData.append('receipt', file);

            const res = await api.post('/receipts/scan', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            scanResult.value = res.data.parsed;
            receiptPath.value = res.data.receipt_path;
            return res.data;
        } catch (e) {
            error.value = e.response?.data?.message || 'Gagal memproses struk.';
            throw e;
        } finally {
            loading.value = false;
        }
    }

    async function save(data) {
        loading.value = true;
        error.value = '';

        try {
            const payload = {
                ...data,
                receipt_path: receiptPath.value,
            };

            const res = await api.post('/receipts/save', payload);
            return res.data;
        } catch (e) {
            error.value = e.response?.data?.message || 'Gagal menyimpan transaksi.';
            throw e;
        } finally {
            loading.value = false;
        }
    }

    function reset() {
        scanResult.value = null;
        receiptPath.value = null;
        error.value = '';
    }

    return { scanResult, receiptPath, loading, error, scan, save, reset };
});
