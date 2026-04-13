<template>
  <div class="p-4 md:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-3">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Categories</h1>
        <p class="text-gray-500 text-sm">Create, edit, and manage categories</p>
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
          <i class="pi pi-plus text-xs"></i> Add Category
        </button>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="p-4 border-b border-gray-100">
        <input
          v-model="search"
          placeholder="Search categories..."
          class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 min-h-[44px]"
        />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
        <div
          v-for="c in filtered"
          :key="c.id"
          class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100"
        >
          <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-10 h-10 rounded-xl flex-shrink-0" :style="{ background: c.color || '#7c3aed' }"></div>
              <div class="min-w-0">
                <div class="font-semibold text-gray-800 truncate">{{ c.name }}</div>
                <div class="text-xs text-gray-500 mt-0.5">#{{ c.slug }}</div>
              </div>
            </div>
            <div class="inline-flex items-center gap-1">
              <button @click="openModal(c)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                <i class="pi pi-pencil text-xs"></i>
              </button>
              <button @click="removeCategory(c)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                <i class="pi pi-trash text-xs"></i>
              </button>
            </div>
          </div>
          <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
            <span>{{ c.products_count ?? 0 }} products</span>
            <span
              class="px-2 py-0.5 rounded-full font-semibold"
              :class="c.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
            >
              {{ c.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
          <p v-if="c.description" class="text-sm text-gray-600 mt-3 line-clamp-2">{{ c.description }}</p>
        </div>
      </div>
    </div>

    <!-- Category Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-end md:items-center justify-center z-50 md:p-4">
      <div class="bg-white w-full h-[92vh] md:h-auto md:rounded-3xl md:max-w-2xl p-4 md:p-6 shadow-2xl max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-bold text-gray-800">{{ editCategory ? 'Edit Category' : 'Add Category' }}</h2>
          <button @click="showModal = false" class="w-11 h-11 rounded-full hover:bg-gray-100 flex items-center justify-center">
            <i class="pi pi-times text-gray-500"></i>
          </button>
        </div>

        <form @submit.prevent="save" class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-20 md:pb-0">
          <div class="col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Category Name *</label>
            <input
              v-model="form.name"
              required
              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300"
              placeholder="e.g. Moti"
            />
          </div>

          <div class="col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
            <textarea
              v-model="form.description"
              rows="2"
              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 resize-none"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Color *</label>
            <div class="flex items-center gap-3">
              <input v-model="form.color" type="color" class="w-12 h-10 rounded-lg border border-gray-200 p-1 cursor-pointer" />
              <input
                v-model="form.color"
                class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300"
                placeholder="#7c3aed"
              />
            </div>
          </div>

          <div class="flex items-end">
            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 cursor-pointer">
              <input type="checkbox" v-model="form.is_active" class="w-4 h-4 accent-purple-600" />
              Active
            </label>
          </div>

          <div class="col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Category Image</label>
            <input type="file" accept="image/*" @change="onFile" class="w-full text-sm text-gray-700" />
          </div>

          <div class="col-span-2 flex gap-3 pt-2 sticky bottom-0 bg-white safe-bottom">
            <button type="button" @click="showModal = false" class="flex-1 py-3 border border-gray-200 rounded-xl text-gray-600 font-semibold hover:bg-gray-50 transition-colors">
              Cancel
            </button>
            <button type="submit" :disabled="saving" class="flex-1 touch-primary bg-purple-600 text-white rounded-xl font-semibold hover:bg-purple-700 disabled:opacity-60">
              {{ saving ? 'Saving...' : (editCategory ? 'Update' : 'Create') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import api from '../lib/axios'
import { apiErrorMessage, confirmAction, showError, showSuccess } from '../lib/swal'

const categories = ref<any[]>([])
const search = ref('')

const showModal = ref(false)
const saving = ref(false)
const editCategory = ref<any | null>(null)
const imageFile = ref<File | null>(null)

const form = ref({
  name: '',
  description: '',
  color: '#7c3aed',
  is_active: true,
  display_order: 0,
})

const filtered = computed(() => {
  const s = search.value.trim().toLowerCase()
  if (!s) return categories.value
  return categories.value.filter((c) => c.name.toLowerCase().includes(s) || c.slug?.toLowerCase().includes(s))
})

async function load() {
  const { data } = await api.get('/categories')
  categories.value = data
}

function openModal(c: any | null = null) {
  editCategory.value = c
  imageFile.value = null
  if (c) {
    form.value = {
      name: c.name ?? '',
      description: c.description ?? '',
      color: c.color ?? '#7c3aed',
      is_active: !!c.is_active,
      display_order: Number(c.display_order ?? 0),
    }
  } else {
    form.value = {
      name: '',
      description: '',
      color: '#7c3aed',
      is_active: true,
      display_order: (categories.value?.length ?? 0) + 1,
    }
  }
  showModal.value = true
}

function onFile(e: Event) {
  const input = e.target as HTMLInputElement
  imageFile.value = input.files?.[0] ?? null
}

async function save() {
  saving.value = true
  try {
    const fd = new FormData()
    fd.append('name', form.value.name)
    fd.append('description', form.value.description || '')
    fd.append('color', form.value.color || '#7c3aed')
    fd.append('is_active', form.value.is_active ? '1' : '0')
    fd.append('display_order', String(form.value.display_order ?? 0))
    if (imageFile.value) fd.append('image', imageFile.value)

    if (editCategory.value?.id) {
      fd.append('_method', 'PUT')
      await api.post(`/categories/${editCategory.value.id}`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    } else {
      await api.post('/categories', fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    }

    showModal.value = false
    await load()
    await showSuccess(`Category ${editCategory.value?.id ? 'updated' : 'created'} successfully.`)
  } catch (e: any) {
    await showError(apiErrorMessage(e, 'Error saving category'))
  } finally {
    saving.value = false
  }
}

async function removeCategory(c: any) {
  if (!(await confirmAction(`Delete "${c.name}"?`, 'Delete category'))) return
  try {
    await api.delete(`/categories/${c.id}`)
    await load()
    await showSuccess('Category deleted successfully.')
  } catch (e: any) {
    await showError(apiErrorMessage(e, 'Cannot delete category'))
  }
}

onMounted(load)
</script>

