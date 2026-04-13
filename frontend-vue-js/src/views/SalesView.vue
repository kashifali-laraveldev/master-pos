<template>
  <div class="p-4 md:p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Sales History</h1>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <input type="date" v-model="filterDate" class="border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none min-h-[44px]" />
        <select v-model="filterStatus" class="border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none min-h-[44px]">
          <option value="">All Status</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
        <button @click="load" class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-semibold hover:bg-purple-700 min-h-[44px]">
          Search
        </button>
      </div>

      <div class="md:hidden p-3 space-y-3">
        <article v-for="sale in sales" :key="sale.id" class="rounded-xl border border-gray-200 p-3 bg-white">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="font-mono text-xs font-semibold text-purple-700">{{ sale.invoice_number }}</p>
              <p class="text-xs text-gray-500">{{ new Date(sale.sold_at).toLocaleDateString() }}</p>
              <p class="text-sm text-gray-700">{{ sale.user?.name }}</p>
            </div>
            <span :class="['px-2 py-1 rounded-full text-xs font-semibold', sale.status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">{{ sale.status }}</span>
          </div>
          <div class="mt-3 flex items-center justify-between text-sm">
            <span>Items: {{ sale.items?.length ?? 0 }}</span>
            <span class="font-bold">₨ {{ Number(sale.total_amount).toLocaleString() }}</span>
          </div>
          <button v-if="sale.status === 'completed'" @click="cancelSale(sale)" class="mt-3 w-full min-h-[44px] rounded-lg border border-red-200 text-red-600 font-semibold">
            Cancel
          </button>
        </article>
      </div>

      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Invoice</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Date</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Cashier</th>
              <th class="text-right py-3 px-4 font-semibold text-gray-600">Items</th>
              <th class="text-right py-3 px-4 font-semibold text-gray-600">Total</th>
              <th class="text-center py-3 px-4 font-semibold text-gray-600">Status</th>
              <th class="text-center py-3 px-4 font-semibold text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="sale in sales" :key="sale.id" class="hover:bg-gray-50 transition-colors">
              <td class="py-3 px-4 font-mono text-xs font-semibold text-purple-700">{{ sale.invoice_number }}</td>
              <td class="py-3 px-4 text-gray-600">{{ new Date(sale.sold_at).toLocaleDateString() }}</td>
              <td class="py-3 px-4 text-gray-600">{{ sale.user?.name }}</td>
              <td class="py-3 px-4 text-right">{{ sale.items?.length ?? 0 }}</td>
              <td class="py-3 px-4 text-right font-bold text-gray-800">₨ {{ Number(sale.total_amount).toLocaleString() }}</td>
              <td class="py-3 px-4 text-center">
                <span
                  :class="[
                    'px-2 py-0.5 rounded-full text-xs font-semibold',
                    sale.status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700',
                  ]"
                >
                  {{ sale.status }}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <button
                  v-if="sale.status === 'completed'"
                  @click="cancelSale(sale)"
                  class="text-xs text-red-500 hover:underline"
                >
                  Cancel
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="meta" class="p-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
        <span>{{ meta.total }} total sales</span>
        <div class="flex gap-2">
          <button @click="page--; load()" :disabled="page <= 1" class="px-3 py-1 border rounded-lg disabled:opacity-40">
            Prev
          </button>
          <span class="px-3 py-1">{{ page }} / {{ meta.last_page }}</span>
          <button @click="page++; load()" :disabled="page >= meta.last_page" class="px-3 py-1 border rounded-lg disabled:opacity-40">
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import api from '../lib/axios'
import { apiErrorMessage, confirmAction, showError, showSuccess } from '../lib/swal'

const sales = ref<any[]>([])
const meta = ref<any>(null)
const page = ref(1)
const filterDate = ref('')
const filterStatus = ref('')

async function load() {
  const { data } = await api.get('/sales', {
    params: {
      page: page.value,
      date: filterDate.value || undefined,
      status: filterStatus.value || undefined,
    },
  })
  sales.value = data.data
  meta.value = { total: data.total, last_page: data.last_page }
}

async function cancelSale(sale: any) {
  if (!(await confirmAction(`Cancel invoice ${sale.invoice_number}? Stock will be restored.`, 'Cancel sale'))) return
  try {
    await api.post(`/sales/${sale.id}/cancel`)
    await load()
    await showSuccess('Sale cancelled and stock restored.')
  } catch (e: any) {
    await showError(apiErrorMessage(e, 'Failed to cancel sale'))
  }
}

onMounted(load)
</script>

