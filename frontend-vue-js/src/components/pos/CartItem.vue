<template>
  <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
    <div class="flex items-start gap-2">
      <img
        v-if="item.image_url"
        :src="item.image_url"
        class="w-10 h-10 rounded-lg object-cover flex-shrink-0"
      />
      <div
        v-else
        class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0"
      >
        <i class="pi pi-box text-purple-400 text-sm"></i>
      </div>

      <div class="flex-1 min-w-0">
        <p class="text-xs font-semibold text-gray-800 leading-tight truncate">{{ item.product_name }}</p>
        <p class="text-xs text-gray-500">
          ₨ {{ Number(item.price_per_unit).toLocaleString() }} / {{ item.unit_label }}
        </p>

        <div class="flex items-center justify-between mt-2">
          <!-- Quantity controls -->
          <div class="flex items-center gap-1">
            <button
              @click="decrement"
              class="w-10 h-10 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-red-50 hover:border-red-200 transition-colors text-gray-600 hover:text-red-500 text-sm font-bold"
            >
              −
            </button>

            <input
              type="number"
              :value="item.quantity"
              @change="emit('update-qty', Number(($event.target as HTMLInputElement).value))"
              class="w-20 h-10 text-center text-sm font-semibold border border-gray-200 rounded-lg py-1 focus:outline-none focus:ring-1 focus:ring-purple-300"
              :step="step"
              min="0.001"
            />

            <button
              @click="increment"
              class="w-10 h-10 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-green-50 hover:border-green-200 transition-colors text-gray-600 hover:text-green-500 text-sm font-bold"
            >
              +
            </button>
          </div>

          <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-purple-700">₨ {{ Number(item.total).toLocaleString() }}</span>
            <button @click="emit('remove')" class="w-10 h-10 rounded-lg text-red-400 hover:text-red-600 transition-colors">
              <i class="pi pi-times text-xs"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ item: any }>()
const emit = defineEmits<{ (e: 'update-qty', qty: number): void; (e: 'remove'): void }>()

const step = computed(() => {
  if (props.item.unit_type === 'piece') return 1
  if (props.item.unit_type === 'dozen') return 1
  return 0.1
})

function increment() {
  const next = Number((props.item.quantity + step.value).toFixed(3))
  emit('update-qty', next)
}

function decrement() {
  const next = Math.max(0.001, Number((props.item.quantity - step.value).toFixed(3)))
  emit('update-qty', next)
}
</script>

