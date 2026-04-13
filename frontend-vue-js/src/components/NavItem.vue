<template>
  <router-link :to="to" v-slot="{ isActive }">
    <div
      :class="[
        'flex items-center gap-3 mx-2 px-3 py-2.5 rounded-xl mb-1 text-sm font-medium transition-all cursor-pointer min-h-[44px]',
        collapsed ? 'justify-center px-2' : '',
        isActive ? 'bg-white/20 text-white shadow-sm' : 'text-purple-200 hover:bg-white/10 hover:text-white',
      ]"
    >
      <i :class="[icon, 'text-sm w-5']"></i>
      <span v-if="!collapsed">{{ label }}</span>
    </div>
  </router-link>
</template>

<script setup lang="ts">
withDefaults(defineProps<{ to: string; icon: string; label: string; collapsed?: boolean }>(), {
  collapsed: false,
})
</script>

