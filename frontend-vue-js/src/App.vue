<template>
  <router-view />
  <div v-if="isGlobalLoading" class="fixed inset-0 z-[3000] bg-black/20 flex items-center justify-center">
    <div class="bg-white rounded-xl px-4 py-3 shadow text-sm font-medium text-gray-700">
      Loading...
    </div>
  </div>
</template>

<script setup lang="ts">
import { isGlobalLoading } from './lib/loading'
</script>

