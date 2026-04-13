import { computed, ref } from 'vue'

const pending = ref(0)

export function startLoading() {
  pending.value += 1
}

export function stopLoading() {
  pending.value = Math.max(0, pending.value - 1)
}

export const isGlobalLoading = computed(() => pending.value > 0)
