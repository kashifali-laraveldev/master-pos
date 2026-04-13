<template>
  <div class="flex flex-col xl:flex-row h-[calc(100vh-56px)] overflow-hidden relative">
    <!-- LEFT: Product Grid -->
    <div class="flex-1 flex flex-col overflow-hidden pb-[140px] md:pb-4">
      <!-- Search & Filters -->
      <div class="bg-white px-3 pt-3 pb-2 mb-2 shadow-sm border-b border-gray-100 sticky top-0 z-20">
        <div class="flex gap-2 sm:gap-3 mb-3">
          <div class="relative flex-1">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input
              v-model="search"
              placeholder="Search products (name, SKU)..."
              class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-400"
            />
          </div>
          <button @click="showCartMobile = true" class="xl:hidden px-3 rounded-xl bg-purple-600 text-white text-sm font-semibold whitespace-nowrap min-h-[44px]">
            Cart
          </button>
        </div>

        <!-- Category Pills -->
        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide whitespace-nowrap">
          <button
            @click="selectedCategory = null"
            :class="[
              'flex-shrink-0 px-4 py-1.5 rounded-full text-xs font-semibold transition-all',
              !selectedCategory ? 'bg-purple-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
            ]"
          >
            All
          </button>

          <button
            v-for="cat in categories"
            :key="cat.id"
            @click="selectedCategory = cat.id"
            :class="[
              'flex-shrink-0 flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold transition-all',
              selectedCategory === cat.id ? 'text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
            ]"
            :style="selectedCategory === cat.id ? `background: ${cat.color}` : ''"
          >
            {{ cat.name }}
          </button>
        </div>
      </div>

      <!-- Products Grid -->
      <div class="flex-1 overflow-y-auto px-3 pb-3">
        <div v-if="loading" class="flex items-center justify-center h-40 text-gray-400">
          <i class="pi pi-spin pi-spinner text-3xl"></i>
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-[10px]">
          <ProductCard
            v-for="p in filteredProducts"
            :key="p.id"
            :product="p"
            @add="openQuantityModal(p)"
          />
        </div>

        <div v-if="!loading && filteredProducts.length === 0" class="flex flex-col items-center justify-center h-40 text-gray-400">
          <i class="pi pi-box text-4xl mb-2"></i>
          <p class="text-sm">No products found</p>
        </div>
      </div>
    </div>

    <!-- RIGHT: Cart Panel -->
    <div
      :class="[
        'bg-white border-gray-200 flex flex-col shadow-xl',
        'w-full xl:w-96 xl:border-l',
        'hidden xl:flex',
      ]"
    >
      <div class="px-5 py-4 border-b border-gray-100">
        <div class="flex items-center justify-between">
          <h2 class="font-bold text-gray-800 flex items-center gap-2 text-sm sm:text-base">
            <i class="pi pi-shopping-cart text-purple-600"></i>
            Cart
          </h2>
          <div class="flex items-center gap-2">
            <span class="bg-purple-100 text-purple-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ cart.itemCount }} items</span>
            <button
              @click="showCartMobile = false"
              class="xl:hidden text-gray-500 hover:text-gray-700 text-sm transition-colors"
              title="Back to products"
            >
              <i class="pi pi-arrow-left"></i>
            </button>
            <button
              @click="cart.clear()"
              v-if="cart.itemCount > 0"
              class="text-red-400 hover:text-red-600 text-sm transition-colors"
            >
              <i class="pi pi-trash text-xs"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="flex-1 overflow-y-auto px-4 py-2">
        <div v-if="cart.itemCount === 0" class="flex flex-col items-center justify-center h-full text-gray-300">
          <i class="pi pi-shopping-cart text-5xl mb-3"></i>
          <p class="text-sm">Cart is empty</p>
          <p class="text-xs mt-1">Click a product to add</p>
        </div>

        <div v-else class="space-y-2 py-2">
          <CartItem
            v-for="item in cart.items"
            :key="item.product_id"
            :item="item"
            @update-qty="cart.updateQuantity(item.product_id, $event)"
            @remove="cart.removeItem(item.product_id)"
          />
        </div>
      </div>

      <div class="border-t border-gray-100 p-4 sm:p-5 space-y-4">
        <div class="flex items-center gap-2">
          <span class="text-sm text-gray-600 w-24 flex-shrink-0">Discount</span>
          <div class="flex-1 relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₨</span>
            <input
              v-model.number="discount"
              type="number"
              min="0"
              placeholder="0"
              class="w-full pl-7 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-300"
            />
          </div>
        </div>

        <div class="bg-gray-50 rounded-xl p-4 space-y-2">
          <div class="flex justify-between text-sm text-gray-600">
            <span>Subtotal</span>
            <span>₨ {{ formatAmount(cart.subtotal) }}</span>
          </div>

          <div class="flex justify-between text-sm text-red-500" v-if="discount > 0">
            <span>Discount</span>
            <span>- ₨ {{ formatAmount(discount) }}</span>
          </div>

          <div class="flex justify-between font-bold text-lg text-gray-800 pt-2 border-t border-gray-200">
            <span>Total</span>
            <span class="text-purple-700">₨ {{ formatAmount(total) }}</span>
          </div>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 block">Payment Method</label>
          <div class="grid grid-cols-2 gap-2">
            <button
              v-for="m in paymentMethods"
              :key="m.value"
              @click="paymentMethod = m.value"
              :class="[
                'flex items-center justify-center gap-1.5 py-2 rounded-xl border text-sm font-medium transition-all',
                paymentMethod === m.value
                  ? 'bg-purple-600 border-purple-600 text-white shadow-md'
                  : 'border-gray-200 text-gray-600 hover:border-purple-300',
              ]"
            >
              <i :class="m.icon + ' text-xs'"></i> {{ m.label }}
            </button>
          </div>
        </div>

        <div v-if="paymentMethod === 'cash'" class="flex items-center gap-2">
          <span class="text-sm text-gray-600 w-24 flex-shrink-0">Received</span>
          <div class="flex-1 relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₨</span>
            <input
              v-model.number="amountPaid"
              type="number"
              :min="total"
              placeholder="Amount given"
              class="w-full pl-7 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-300"
            />
          </div>
        </div>

        <div
          v-if="paymentMethod === 'cash' && change >= 0 && amountPaid > 0"
          class="flex justify-between text-sm font-semibold text-green-600 bg-green-50 px-3 py-2 rounded-lg"
        >
          <span>Change</span><span>₨ {{ formatAmount(change) }}</span>
        </div>

        <button
          @click="checkout"
          :disabled="cart.itemCount === 0 || processing"
          class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-3.5 sm:py-4 rounded-2xl transition-all shadow-lg shadow-purple-200 disabled:opacity-50 flex items-center justify-center gap-2 text-sm sm:text-base"
        >
          <i v-if="processing" class="pi pi-spin pi-spinner"></i>
          <i v-else class="pi pi-check-circle"></i>
          {{ processing ? 'Processing...' : 'Complete Sale' }}
        </button>
      </div>
    </div>

    <!-- Mobile sticky cart bar -->
    <button
      v-if="!showCartMobile"
      @click="showCartMobile = true"
      class="xl:hidden fixed left-3 right-3 bottom-[64px] z-[200] bg-purple-700 text-white rounded-2xl px-4 min-h-[56px] flex items-center justify-between shadow-lg"
    >
      <span class="font-semibold text-sm flex items-center gap-2"><i class="pi pi-shopping-cart"></i>View Cart ({{ cart.itemCount }})</span>
      <span class="font-bold text-sm">₨ {{ formatAmount(total) }}</span>
    </button>

    <!-- Mobile cart bottom sheet -->
    <div v-if="showCartMobile" class="xl:hidden fixed inset-0 z-[1000]">
      <div class="absolute inset-0 bg-black/50" @click="showCartMobile = false"></div>
      <section class="absolute bottom-[64px] left-0 right-0 h-[82vh] bg-white rounded-t-[20px] shadow-2xl flex flex-col transition-all duration-300">
        <div class="pt-2 pb-3 px-4 border-b border-gray-100">
          <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-3"></div>
          <div class="flex items-center justify-between">
            <h2 class="font-bold text-gray-800 flex items-center gap-2">
              <i class="pi pi-shopping-cart text-purple-600"></i>
              Cart
            </h2>
            <button @click="showCartMobile = false" class="w-11 h-11 rounded-full hover:bg-gray-100 flex items-center justify-center">
              <i class="pi pi-times"></i>
            </button>
          </div>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-3">
          <div v-if="cart.itemCount === 0" class="h-full flex items-center justify-center text-gray-400 text-sm">
            <div class="text-center">
              <i class="pi pi-shopping-cart text-3xl mb-2"></i>
              <p>Cart is empty, tap a product to add</p>
            </div>
          </div>
          <div v-else class="space-y-3">
            <CartItem
              v-for="item in cart.items"
              :key="item.product_id"
              :item="item"
              @update-qty="cart.updateQuantity(item.product_id, $event)"
              @remove="cart.removeItem(item.product_id)"
            />
          </div>
        </div>

        <div class="p-3 border-t border-gray-100 bg-white">
          <div class="flex items-center gap-2 mb-2">
            <span class="text-sm text-gray-600 w-20 flex-shrink-0">Discount</span>
            <div class="flex-1 relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₨</span>
              <input
                v-model.number="discount"
                type="number"
                min="0"
                placeholder="0"
                class="w-full min-h-[40px] pl-7 pr-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-300"
              />
            </div>
          </div>

          <div class="bg-gray-50 rounded-xl p-2.5 mb-2">
            <div class="flex justify-between text-sm"><span>Subtotal</span><span>₨ {{ formatAmount(cart.subtotal) }}</span></div>
            <div class="flex justify-between text-sm text-red-500" v-if="discount > 0"><span>Discount</span><span>- ₨ {{ formatAmount(discount) }}</span></div>
            <div class="flex justify-between font-bold text-base mt-1.5 pt-1.5 border-t border-gray-200"><span>Total</span><span>₨ {{ formatAmount(total) }}</span></div>
          </div>
          <div class="space-y-1.5 mb-2">
            <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Payment Method</label>
            <div class="grid grid-cols-2 gap-2">
              <button
                v-for="m in paymentMethods"
                :key="'m-' + m.value"
                @click="paymentMethod = m.value"
                :class="[
                  'min-h-[40px] rounded-xl border text-sm font-medium',
                  paymentMethod === m.value ? 'bg-purple-600 border-purple-600 text-white' : 'border-gray-200 text-gray-600',
                ]"
              >
                {{ m.label }}
              </button>
            </div>
          </div>
          <div v-if="paymentMethod === 'cash'" class="mb-2 flex items-center gap-2">
            <span class="text-sm text-gray-600 w-20 flex-shrink-0">Received</span>
            <input
              v-model.number="amountPaid"
              type="number"
              :min="total"
              placeholder="0"
              class="flex-1 min-h-[40px] px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-300"
            />
          </div>
          <div
            v-if="paymentMethod === 'cash' && change >= 0 && amountPaid > 0"
            class="flex justify-between text-sm font-semibold text-green-600 bg-green-50 px-3 py-1.5 rounded-lg mb-2"
          >
            <span>Change</span><span>₨ {{ formatAmount(change) }}</span>
          </div>
          <button @click="checkout" :disabled="cart.itemCount===0 || processing" class="w-full min-h-[48px] bg-purple-600 text-white rounded-xl font-semibold disabled:opacity-60">
            {{ processing ? 'Processing...' : 'Complete Sale' }}
          </button>
        </div>
      </section>
    </div>

    <QuantityModal
      v-if="selectedProduct"
      :product="selectedProduct"
      @close="selectedProduct = null"
      @add="addToCart"
    />

    <ReceiptModal v-if="lastSale" :sale="lastSale" @close="lastSale = null" />
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import api, { API_ORIGIN } from '../lib/axios'
import { useCartStore } from '../stores/cart'

import ProductCard from '../components/pos/ProductCard.vue'
import CartItem from '../components/pos/CartItem.vue'
import QuantityModal from '../components/pos/QuantityModal.vue'
import ReceiptModal from '../components/pos/ReceiptModal.vue'
import { apiErrorMessage, showError, showSuccess } from '../lib/swal'

const cart = useCartStore()

const products = ref<any[]>([])
const categories = ref<any[]>([])
const selectedCategory = ref<number | null>(null)
const search = ref('')
const loading = ref(false)
const processing = ref(false)
const selectedProduct = ref<any | null>(null)
const lastSale = ref<any | null>(null)

const discount = ref(0)
const paymentMethod = ref('cash')
const amountPaid = ref(0)
const showCartMobile = ref(false)

const paymentMethods = [
  { value: 'cash', icon: 'pi pi-money-bill', label: 'Cash' },
  { value: 'card', icon: 'pi pi-credit-card', label: 'Card' },
  { value: 'bank_transfer', icon: 'pi pi-building', label: 'Bank' },
  { value: 'credit', icon: 'pi pi-clock', label: 'Credit' },
]

const total = computed(() => Math.max(0, cart.subtotal - discount.value))
const change = computed(() => amountPaid.value - total.value)

const filteredProducts = computed(() =>
  products.value.filter((p) => {
    const matchCat = !selectedCategory.value || p.category_id === selectedCategory.value
    const s = search.value.trim().toLowerCase()
    const matchSrch = !s || p.name.toLowerCase().includes(s) || p.sku?.toString().includes(s)
    return matchCat && matchSrch
  }),
)

function formatAmount(n: any) {
  return Number(n || 0).toLocaleString('en-PK', { minimumFractionDigits: 0 })
}

function openQuantityModal(product: any) {
  selectedProduct.value = product
}

function addToCart({ product, quantity }: { product: any; quantity: number }) {
  cart.addItem(product, quantity)
  selectedProduct.value = null
}

async function checkout() {
  if (cart.itemCount === 0) return
  if (paymentMethod.value === 'cash' && amountPaid.value < total.value) {
    await showError('Amount paid is less than total!')
    return
  }

  processing.value = true
  try {
    const { data } = await api.post('/sales', {
      items: cart.items.map((i) => ({ product_id: i.product_id, quantity: i.quantity })),
      discount_amount: discount.value || 0,
      payment_method: paymentMethod.value,
      amount_paid: paymentMethod.value === 'cash' ? amountPaid.value : total.value,
    })

    const { data: fullSale } = await api.get(`/sales/${data.id}`)
    lastSale.value = fullSale

    cart.clear()
    discount.value = 0
    amountPaid.value = 0
    paymentMethod.value = 'cash'
    showCartMobile.value = false

    await loadProducts()
    await showSuccess('Sale completed successfully.')
  } catch (e: any) {
    await showError(apiErrorMessage(e, 'Sale failed!'))
  } finally {
    processing.value = false
  }
}

async function loadProducts() {
  loading.value = true
  try {
    const { data } = await api.get('/products', { params: { is_active: 1 } })
    products.value = data.map((p: any) => ({
      ...p,
      image_url: p.image ? `${API_ORIGIN}/storage/${p.image}` : p.image_url ?? null,
    }))
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadProducts()
  const { data } = await api.get('/categories')
  categories.value = data
})
</script>

