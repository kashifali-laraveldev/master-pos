import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

type CartItem = {
  product_id: number
  product_name: string
  unit_type: string
  unit_label: string
  price_per_unit: number
  quantity: number
  total: number
  image_url?: string | null
}

export const useCartStore = defineStore('cart', () => {
  const items = ref<CartItem[]>([])

  function addItem(product: any, quantity: number) {
    const existing = items.value.find((i) => i.product_id === product.id)
    if (existing) {
      existing.quantity = parseFloat((existing.quantity + quantity).toFixed(3))
      existing.total = parseFloat((existing.quantity * existing.price_per_unit).toFixed(2))
      return
    }

    items.value.push({
      product_id: product.id,
      product_name: product.name,
      unit_type: product.unit_type,
      unit_label: product.unit_label,
      price_per_unit: Number(product.price_per_unit),
      quantity: parseFloat(quantity.toFixed(3)),
      total: parseFloat((quantity * Number(product.price_per_unit)).toFixed(2)),
      image_url: product.image_url,
    })
  }

  function updateQuantity(productId: number, qty: number) {
    const item = items.value.find((i) => i.product_id === productId)
    if (!item) return

    item.quantity = parseFloat((qty as number).toFixed(3))
    item.total = parseFloat((item.quantity * item.price_per_unit).toFixed(2))
  }

  function removeItem(productId: number) {
    items.value = items.value.filter((i) => i.product_id !== productId)
  }

  function clear() {
    items.value = []
  }

  const subtotal = computed(() => items.value.reduce((s, i) => s + i.total, 0))
  const itemCount = computed(() => items.value.length)

  return { items, addItem, updateQuantity, removeItem, clear, subtotal, itemCount }
})

