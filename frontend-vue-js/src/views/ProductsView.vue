<template>
  <div class="p-4 md:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-3">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Products</h1>
        <p class="text-gray-500 text-sm">Create, edit, deactivate products</p>
      </div>
      <div class="flex items-center gap-3">
        <button
          @click="load"
          class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 min-h-[44px]"
        >
          Refresh
        </button>
        <button
          @click="openModal()"
          class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-semibold hover:bg-purple-700 flex items-center gap-2 min-h-[44px]"
        >
          <i class="pi pi-plus text-xs"></i> Add Product
        </button>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row gap-3">
        <input
          v-model="search"
          placeholder="Search by name or SKU..."
          class="flex-1 min-w-[220px] border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 min-h-[44px]"
        />
        <select
          v-model="filterUnitType"
          class="border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 min-h-[44px]"
        >
          <option value="">All Unit Types</option>
          <option value="weight">Weight</option>
          <option value="length">Length</option>
          <option value="piece">Piece</option>
          <option value="dozen">Dozen</option>
        </select>
      </div>

      <div class="md:hidden p-3 space-y-3">
        <article v-for="p in filtered" :key="p.id" class="rounded-xl border border-gray-200 p-3">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <p class="font-semibold text-gray-800 truncate">{{ p.name }}</p>
              <p class="text-xs text-gray-400">{{ p.sku }}</p>
            </div>
            <span class="text-sm font-bold text-purple-700">₨ {{ Number(p.price_per_unit).toLocaleString() }}</span>
          </div>
          <div class="mt-2 flex items-center justify-between text-xs">
            <span class="px-2 py-1 rounded-full text-white font-semibold" :style="{ background: p.category?.color || '#7c3aed' }">{{ p.category?.name || '-' }}</span>
            <span :class="p.stock_quantity <= p.low_stock_alert ? 'text-red-600 font-bold' : 'text-green-700 font-bold'">{{ Number(p.stock_quantity).toFixed(1) }} {{ p.unit_label }}</span>
          </div>
          <div class="mt-3 grid grid-cols-2 gap-2">
            <button @click="openModal(p)" class="min-h-[44px] rounded-lg border border-blue-200 text-blue-600 font-semibold">Edit</button>
            <button @click="deactivate(p)" class="min-h-[44px] rounded-lg border border-red-200 text-red-600 font-semibold">Deactivate</button>
          </div>
        </article>
      </div>

      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Product</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Category</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Unit</th>
              <th class="text-right py-3 px-4 font-semibold text-gray-600">Price</th>
              <th class="text-right py-3 px-4 font-semibold text-gray-600">Stock</th>
              <th class="text-center py-3 px-4 font-semibold text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr
              v-for="p in filtered"
              :key="p.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="py-3 px-4">
                <div class="font-semibold text-gray-800 truncate max-w-[240px]">{{ p.name }}</div>
                <div class="text-xs text-gray-400">{{ p.sku }}</div>
                <div v-if="p.is_featured" class="mt-1 inline-flex items-center gap-1">
                  <span class="px-2 py-0.5 rounded-full text-xs font-semibold" :style="{ background: '#f5f3ff', color: '#6d28d9' }">
                    Featured
                  </span>
                </div>
              </td>
              <td class="py-3 px-4">
                <span class="px-2 py-1 rounded-full text-xs font-semibold text-white" :style="{ background: p.category?.color || '#7c3aed' }">
                  {{ p.category?.name || '-' }}
                </span>
              </td>
              <td class="py-3 px-4 text-gray-600 capitalize">
                {{ p.unit_type }} ({{ p.unit_label }})
              </td>
              <td class="py-3 px-4 text-right font-semibold text-purple-700">₨ {{ Number(p.price_per_unit).toLocaleString() }}</td>
              <td class="py-3 px-4 text-right">
                <span :class="p.stock_quantity <= p.low_stock_alert ? 'text-red-600 font-bold' : 'text-green-700 font-bold'">
                  {{ Number(p.stock_quantity).toFixed(1) }} {{ p.unit_label }}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                  <button @click="openModal(p)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                    <i class="pi pi-pencil text-xs"></i>
                  </button>
                  <button
                    @click="deactivate(p)"
                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    title="Deactivate"
                  >
                    <i class="pi pi-trash text-xs"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-end md:items-center justify-center z-50 md:p-4">
      <div class="bg-white w-full h-[92vh] md:h-auto md:rounded-3xl md:max-w-3xl p-4 md:p-6 shadow-2xl overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-bold text-gray-800">{{ editProduct ? 'Edit Product' : 'Add Product' }}</h2>
          <button @click="showModal = false" class="w-11 h-11 rounded-full hover:bg-gray-100 flex items-center justify-center">
            <i class="pi pi-times text-gray-500"></i>
          </button>
        </div>

        <form @submit.prevent="save" class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-20 md:pb-0">
          <div class="col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Product Name *</label>
            <input v-model="form.name" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 min-h-[44px]" />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Category *</label>
            <select v-model.number="form.category_id" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 min-h-[44px]">
              <option :value="0" disabled>Select...</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">SKU</label>
            <input v-model="form.sku" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 min-h-[44px]" placeholder="e.g. MTI-001" />
          </div>

          <div class="col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
            <textarea v-model="form.description" rows="2" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 resize-none"></textarea>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Unit Type *</label>
            <select v-model="form.unit_type" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300">
              <option value="weight">Weight</option>
              <option value="length">Length</option>
              <option value="piece">Piece</option>
              <option value="dozen">Dozen</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Unit Label *</label>
            <select v-model="form.unit_label" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300">
              <option v-for="u in unitLabelOptions" :key="u" :value="u">{{ u }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Price per Unit (₨) *</label>
            <input v-model.number="form.price_per_unit" type="number" min="0" step="0.01" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300" />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Cost Price (₨)</label>
            <input v-model.number="form.cost_price" type="number" min="0" step="0.01" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300" />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Opening Stock *</label>
            <input v-model.number="form.stock_quantity" type="number" min="0" step="0.001" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300" />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Low Stock Alert</label>
            <input v-model.number="form.low_stock_alert" type="number" min="0" step="0.001" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300" />
          </div>

          <div class="col-span-2">
            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-1.5 cursor-pointer">
              <input type="checkbox" v-model="form.is_featured" class="w-4 h-4 accent-purple-600" />
              Featured Product
            </label>
          </div>

          <div class="col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Product Image</label>
            <input type="file" accept="image/*" @change="onFile" class="w-full text-sm text-gray-700" />
          </div>

          <div class="col-span-2 flex gap-3 pt-2 sticky bottom-0 bg-white safe-bottom">
            <button type="button" @click="showModal = false" class="flex-1 py-3 border border-gray-200 rounded-xl text-gray-600 font-semibold hover:bg-gray-50 transition-colors">
              Cancel
            </button>
            <button type="submit" :disabled="saving" class="flex-1 touch-primary bg-purple-600 text-white rounded-xl font-semibold hover:bg-purple-700 disabled:opacity-60">
              {{ saving ? 'Saving...' : (editProduct ? 'Update' : 'Create') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import api from '../lib/axios'
import { apiErrorMessage, confirmAction, showError, showSuccess } from '../lib/swal'

const products = ref<any[]>([])
const categories = ref<any[]>([])
const search = ref('')
const filterUnitType = ref<string>('')

const showModal = ref(false)
const saving = ref(false)
const editProduct = ref<any | null>(null)
const imageFile = ref<File | null>(null)

const form = ref({
  name: '',
  category_id: 0,
  sku: '',
  description: '',
  unit_type: 'piece' as 'weight' | 'length' | 'piece' | 'dozen',
  unit_label: 'piece',
  stock_quantity: 0,
  low_stock_alert: 10,
  price_per_unit: 0,
  cost_price: 0,
  stock_unit: 'piece',
  is_featured: false,
})

const unitLabelOptions = computed(() => {
  const t = form.value.unit_type
  if (t === 'weight') return ['tola', 'gram', 'kg']
  if (t === 'length') return ['gaz', 'meter', 'yard']
  if (t === 'piece') return ['piece', 'unit']
  if (t === 'dozen') return ['dozen', 'set']
  return ['piece']
})

watch(
  () => form.value.unit_type,
  () => {
    const opts = unitLabelOptions.value
    if (!opts.includes(form.value.unit_label)) {
      form.value.unit_label = opts[0]
    }
    form.value.stock_unit = form.value.unit_label
  },
)

const filtered = computed(() => {
  const s = search.value.trim().toLowerCase()
  return products.value.filter((p) => {
    const matchS = !s || p.name.toLowerCase().includes(s) || p.sku?.toString().includes(s)
    const matchU = !filterUnitType.value || p.unit_type === filterUnitType.value
    return matchS && matchU
  })
})

function openModal(p: any | null = null) {
  editProduct.value = p
  imageFile.value = null
  if (p) {
    form.value = {
      name: p.name ?? '',
      category_id: Number(p.category_id ?? p.category?.id ?? 0),
      sku: p.sku ?? '',
      description: p.description ?? '',
      unit_type: p.unit_type ?? 'piece',
      unit_label: p.unit_label ?? 'piece',
      stock_quantity: Number(p.stock_quantity ?? 0),
      low_stock_alert: Number(p.low_stock_alert ?? 10),
      price_per_unit: Number(p.price_per_unit ?? 0),
      cost_price: Number(p.cost_price ?? 0),
      stock_unit: p.stock_unit ?? p.unit_label ?? 'piece',
      is_featured: !!p.is_featured,
    }
  } else {
    form.value = {
      name: '',
      category_id: categories.value[0]?.id ?? 0,
      sku: '',
      description: '',
      unit_type: 'piece',
      unit_label: 'piece',
      stock_quantity: 0,
      low_stock_alert: 10,
      price_per_unit: 0,
      cost_price: 0,
      stock_unit: 'piece',
      is_featured: false,
    }
  }
  showModal.value = true
}

function onFile(e: Event) {
  const input = e.target as HTMLInputElement
  imageFile.value = input.files?.[0] ?? null
}

async function load() {
  const { data: prods } = await api.get('/products')
  products.value = prods
  const { data: cats } = await api.get('/categories')
  categories.value = cats
}

async function deactivate(p: any) {
  if (!(await confirmAction(`Deactivate ${p.name}?`, 'Deactivate product'))) return
  await api.delete(`/products/${p.id}`)
  await load()
  await showSuccess('Product deactivated successfully.')
}

async function save() {
  saving.value = true
  try {
    const fd = new FormData()
    fd.append('category_id', String(form.value.category_id))
    fd.append('name', form.value.name)
    if (form.value.sku) fd.append('sku', form.value.sku)
    fd.append('description', form.value.description || '')

    fd.append('unit_type', form.value.unit_type)
    fd.append('unit_label', form.value.unit_label)
    fd.append('stock_unit', form.value.unit_label)

    fd.append('price_per_unit', String(form.value.price_per_unit))
    fd.append('cost_price', form.value.cost_price === 0 ? '0' : String(form.value.cost_price))
    fd.append('stock_quantity', String(form.value.stock_quantity))
    fd.append('low_stock_alert', String(form.value.low_stock_alert))
    fd.append('is_featured', form.value.is_featured ? '1' : '0')

    if (imageFile.value) fd.append('image', imageFile.value)

    if (editProduct.value?.id) {
      fd.append('_method', 'PUT')
      await api.post(`/products/${editProduct.value.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    } else {
      await api.post('/products', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    }

    showModal.value = false
    await load()
    await showSuccess(`Product ${editProduct.value?.id ? 'updated' : 'created'} successfully.`)
  } catch (e: any) {
    await showError(apiErrorMessage(e, 'Error saving product'))
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

