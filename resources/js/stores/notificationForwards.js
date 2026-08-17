import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/composables/useApi'

export const useNotificationForwardsStore = defineStore('notificationForwards', () => {
  const forwards = ref([])
  const pagination = ref(null)
  const loading = ref(false)
  const pendingCount = ref(0)

  const hasUnread = computed(() => pendingCount.value > 0)

  async function fetchAll(params = {}) {
    loading.value = true
    try {
      const { data } = await api.get('/notification-forwards', { params })
      forwards.value = data.data
      pagination.value = {
        current_page: data.current_page,
        last_page: data.last_page,
        per_page: data.per_page,
        total: data.total,
      }
    } finally {
      loading.value = false
    }
  }

  async function fetchPendingCount() {
    try {
      const { data } = await api.get('/notification-forwards/pending-count')
      pendingCount.value = data.count
    } catch {
      // ignore
    }
  }

  async function confirm(id, transactionData) {
    const { data } = await api.post(`/notification-forwards/${id}/confirm`, transactionData)
    const idx = forwards.value.findIndex(f => f.id === id)
    if (idx !== -1) {
      forwards.value[idx].status = 'confirmed'
      forwards.value[idx].transaction_id = data.id
    }
    pendingCount.value = Math.max(0, pendingCount.value - 1)
    return data
  }

  async function ignore(id) {
    await api.post(`/notification-forwards/${id}/ignore`)
    const idx = forwards.value.findIndex(f => f.id === id)
    if (idx !== -1) {
      forwards.value[idx].status = 'ignored'
    }
    pendingCount.value = Math.max(0, pendingCount.value - 1)
  }

  return {
    forwards,
    pagination,
    loading,
    pendingCount,
    hasUnread,
    fetchAll,
    fetchPendingCount,
    confirm,
    ignore,
  }
})
