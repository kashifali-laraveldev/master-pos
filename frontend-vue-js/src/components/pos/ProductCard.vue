<template>
  <div
    @click="$emit('add')"
    class="bg-white rounded-xl overflow-hidden border border-gray-100 hover:border-purple-300 hover:shadow-lg cursor-pointer transition-all group min-h-[140px] active:scale-[0.98]"
  >
    <div class="aspect-square bg-gradient-to-br from-purple-50 to-indigo-50 relative overflow-hidden">
      <img
        v-if="product.image_url"
        :src="product.image_url"
        :alt="product.name"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
      />
      <div v-else class="w-full h-full flex items-center justify-center">
        <i class="pi pi-image text-3xl text-purple-200"></i>
      </div>

      <div
        v-if="product.stock_quantity <= product.low_stock_alert"
        class="absolute top-2 right-2 bg-amber-400 text-amber-900 text-xs font-bold px-1.5 py-0.5 rounded-full"
      >
        Low Stock
      </div>

      <div class="absolute bottom-2 left-2 bg-purple-600 text-white text-xs font-semibold px-2 py-0.5 rounded-full capitalize">
        per {{ product.unit_label }}
      </div>
    </div>

    <div class="p-2.5">
      <h3 class="text-sm font-semibold text-gray-800 leading-tight line-clamp-2 min-h-[2.4rem] mb-1">{{ product.name }}</h3>
      <div class="flex items-center justify-between">
        <span class="text-purple-700 font-bold text-sm">₨ {{ Number(product.price_per_unit).toLocaleString() }}</span>
        <span class="text-gray-400 text-xs">{{ Number(product.stock_quantity).toFixed(1) }} {{ product.unit_label }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{ product: any }>()
defineEmits<{ (e: 'add'): void }>()
</script>

