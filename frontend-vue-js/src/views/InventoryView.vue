<template>
  <div class="p-4 md:p-6">
    <div class="flex items-center justify-between mb-6 gap-3">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Inventory</h1>
        <p class="text-gray-500 text-sm">Adjust stock using purchase/adjustment/return</p>
      </div>
      <button @click="load" class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-semibold hover:bg-purple-700 min-h-[44px]">
        Refresh
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="md:hidden space-y-3">
          <article v-for="p in products" :key="p.id" class="rounded-xl border border-gray-200 p-3">
            <div class="font-semibold text-gray-800">{{ p.name }}</div>
            <div class="text-xs text-gray-400">{{ p.sku }}</div>
            <div class="mt-2 text-sm text-right" :class="p.stock_quantity <= p.low_stock_alert ? 'text-red-600 font-bold' : 'text-green-700 font-bold'">
              {{ Number(p.stock_quantity).toFixed(1) }} {{ p.unit_label }}
            </div>
          </article>
        </div>
        <div class="hidden md:block overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="text-left py-3 px-4 font-semibold text-gray-600">Product</th>
                <th class="text-right py-3 px-4 font-semibold text-gray-600">Stock</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="p in products" :key="p.id" class="hover:bg-gray-50 transition-colors">
                <td class="py-3 px-4">
                  <div class="font-semibold text-gray-800">{{ p.name }}</div>
                  <div class="text-xs text-gray-400">{{ p.sku }}</div>
                </td>
                <td class="py-3 px-4 text-right">
                  <span :class="p.stock_quantity <= p.low_stock_alert ? 'text-red-600 font-bold' : 'text-green-700 font-bold'">
                    {{ Number(p.stock_quantity).toFixed(1) }} {{ p.unit_label }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <h2 class="font-bold text-gray-800 mb-4">Stock Adjustment</h2>

        <div class="space-y-3">
          <div>
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 block">Product</label>
            <select
              v-model.number="selectedProductId"
              class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 min-h-[44px]"
            >
              <option v-for="p in products" :key="p.id" :value="p.id">
                {{ p.name }}
              </option>
            </select>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Current</span>
            <span class="text-sm font-semibold text-purple-700">
              {{ selectedProduct?.stock_quantity ?? 0 }} {{ selectedProduct?.unit_label }}
            </span>
          </div>

          <div>
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 block">Type</label>
            <select
              v-model="form.type"
              class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 min-h-[44px]"
            >
              <option value="purchase">purchase (+)</option>
              <option value="adjustment">adjustment (-/+) </option>
              <option value="return">return (+)</option>
            </select>
          </div>

          <div>
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 block">
              Quantity (use negative for deduction)
            </label>
            <input
              v-model.number="form.quantity"
              type="number"
              step="0.001"
              class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 min-h-[44px]"
            />
          </div>

          <div>
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 block">Notes</label>
            <input
              v-model="form.notes"
              class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 min-h-[44px]"
              placeholder="Reason for adjustment..."
            />
          </div>

          <button
            @click="save"
            class="w-full min-h-[48px] px-4 bg-purple-600 text-white rounded-xl font-semibold hover:bg-purple-700 disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center justify-center text-sm sm:text-base whitespace-nowrap"
            :disabled="!selectedProductId || !form.quantity"
          >
            Save Adjustment
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import api from '../lib/axios'
import { apiErrorMessage, showError, showSuccess } from '../lib/swal'

const products = ref<any[]>([])
const selectedProductId = ref<number | null>(null)
const form = ref({ type: 'purchase', quantity: 0, notes: '' })

const selectedProduct = computed(() => products.value.find((p) => p.id === selectedProductId.value))

async function load() {
  const { data } = await api.get('/products', { params: { is_active: 1 } })
  products.value = data
  if (!selectedProductId.value && products.value.length) {
    selectedProductId.value = products.value[0].id
  }
}

async function save() {
  if (!selectedProductId.value) return
  try {
    await api.post(`/products/${selectedProductId.value}/adjust-stock`, form.value)
    await load()
    form.value = { ...form.value, quantity: 0 }
    await showSuccess('Stock adjustment saved successfully.')
  } catch (e: any) {
    await showError(apiErrorMessage(e, 'Adjustment failed'))
  }
}

onMounted(load)
</script>

