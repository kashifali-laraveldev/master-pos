<template>
  <div class="fixed inset-0 bg-black/50 flex items-end sm:items-center justify-center z-[2000] sm:p-4">
    <div class="bg-white w-full h-[90vh] sm:h-auto sm:rounded-3xl sm:max-w-sm p-5 sm:p-6 shadow-2xl overflow-y-auto">
      <div class="flex items-center gap-4 mb-6">
        <img
          v-if="product.image_url"
          :src="product.image_url"
          class="w-16 h-16 rounded-xl object-cover"
        />
        <div v-else class="w-16 h-16 rounded-xl bg-purple-100 flex items-center justify-center">
          <i class="pi pi-box text-purple-400 text-2xl"></i>
        </div>

        <div>
          <h3 class="font-bold text-gray-800">{{ product.name }}</h3>
          <p class="text-purple-600 font-semibold text-sm">
            ₨ {{ Number(product.price_per_unit).toLocaleString() }} / {{ product.unit_label }}
          </p>
          <p class="text-gray-400 text-xs">
            Stock: {{ Number(product.stock_quantity).toFixed(1) }} {{ product.unit_label }}
          </p>
        </div>
      </div>

      <div class="mb-6">
        <label class="block text-sm font-semibold text-gray-700 mb-3 capitalize">Enter {{ unitLabel }}</label>

        <div v-if="product.unit_type === 'weight'" class="mb-3">
          <div class="flex gap-2 mb-3">
            <button
              v-for="u in weightUnits"
              :key="u.val"
              @click="selectedSubUnit = u.val"
              :class="[
                'flex-1 py-2 rounded-xl text-sm font-semibold border transition-all',
                selectedSubUnit === u.val ? 'bg-purple-600 border-purple-600 text-white' : 'border-gray-200 text-gray-600',
              ]"
            >
              {{ u.label }}
            </button>
          </div>
        </div>

        <div v-if="product.unit_type === 'length'" class="mb-3">
          <div class="flex gap-2 mb-3">
            <button
              v-for="u in lengthUnits"
              :key="u.val"
              @click="selectedSubUnit = u.val"
              :class="[
                'flex-1 py-2 rounded-xl text-sm font-semibold border transition-all',
                selectedSubUnit === u.val ? 'bg-purple-600 border-purple-600 text-white' : 'border-gray-200 text-gray-600',
              ]"
            >
              {{ u.label }}
            </button>
          </div>
        </div>

        <div class="relative">
          <input
            v-model="quantity"
            type="number"
            :min="minQty"
            :step="step"
            placeholder="0"
            class="w-full text-center text-3xl font-bold border-2 border-purple-200 rounded-2xl py-4 focus:outline-none focus:border-purple-500 transition-colors text-[16px]"
            autofocus
          />
          <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">
            {{ displayUnit }}
          </span>
        </div>

        <div class="grid grid-cols-4 gap-2 mt-3">
          <button
            v-for="q in quickQtys"
            :key="q"
            @click="quantity = q"
            class="py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:border-purple-300 hover:text-purple-600 transition-all"
          >
            {{ q }}
          </button>
        </div>

        <div v-if="quantity > 0" class="mt-4 bg-purple-50 rounded-xl p-3 text-center">
          <span class="text-sm text-gray-600">Total: </span>
          <span class="text-purple-700 font-bold text-lg">₨ {{ totalPreview }}</span>
        </div>
      </div>

      <div class="flex gap-3 sticky bottom-0 bg-white pt-3 safe-bottom">
        <button
          @click="$emit('close')"
          class="flex-1 min-h-[52px] border border-gray-200 rounded-2xl text-gray-600 font-semibold hover:bg-gray-50 transition-colors"
        >
          Cancel
        </button>
        <button
          @click="addItem"
          :disabled="!quantity || Number(quantity) <= 0"
          class="flex-1 min-h-[52px] bg-purple-600 text-white rounded-2xl font-semibold hover:bg-purple-700 transition-colors shadow-lg shadow-purple-200 disabled:opacity-50 flex items-center justify-center gap-2"
        >
          <i class="pi pi-plus text-sm"></i> Add to Cart
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'

const props = defineProps<{ product: any }>()
const emit = defineEmits<{ (e: 'close'): void; (e: 'add', payload: { product: any; quantity: number }): void }>()

const quantity = ref<string | number>('')
const selectedSubUnit = ref(props.product.unit_label)

const weightUnits = [
  { val: 'tola', label: 'Tola (11.66g)' },
  { val: 'gram', label: 'Gram' },
  { val: 'kg', label: 'KG' },
]

const lengthUnits = [
  { val: 'gaz', label: 'Gaz' },
  { val: 'meter', label: 'Meter' },
  { val: 'yard', label: 'Yard' },
]

const step = computed(() => (props.product.unit_type === 'piece' ? 1 : 0.1))
const minQty = computed(() => (props.product.unit_type === 'piece' ? 1 : 0.1))

const displayUnit = computed(() => selectedSubUnit.value || props.product.unit_label)

const quickQtys = computed(() => {
  if (props.product.unit_type === 'piece') return [1, 5, 10, 20]
  if (props.product.unit_type === 'dozen') return [1, 2, 5, 10]
  if (props.product.unit_type === 'weight') return [0.5, 1, 2, 5]
  return [0.5, 1, 2, 5]
})

// Convert entered sub-unit into the product's base unit (price_per_unit & stock_quantity unit).
const convertedQty = computed(() => {
  const q = parseFloat(String(quantity.value)) || 0

  if (props.product.unit_type === 'weight') {
    if (selectedSubUnit.value === 'gram') return q / 11.66
    if (selectedSubUnit.value === 'kg') return (q * 1000) / 11.66
    return q // tola
  }

  if (props.product.unit_type === 'length') {
    if (selectedSubUnit.value === 'meter') return q / 0.9144
    if (selectedSubUnit.value === 'yard') return q // approx: 1 yard ~= 1 gaz
    return q // gaz
  }

  return q
})

const totalPreview = computed(() => {
  return (convertedQty.value * Number(props.product.price_per_unit)).toLocaleString('en-PK', { minimumFractionDigits: 0 })
})

const unitLabel = computed(() => {
  if (props.product.unit_type === 'weight') return 'Weight'
  if (props.product.unit_type === 'length') return 'Length'
  if (props.product.unit_type === 'dozen') return 'Dozens'
  return 'Quantity'
})

function addItem() {
  const q = parseFloat(String(quantity.value))
  if (!q || q <= 0) return

  emit('add', { product: props.product, quantity: convertedQty.value })
}
</script>

