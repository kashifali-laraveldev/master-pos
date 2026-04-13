<template>
  <div class="p-4 md:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-3">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Users</h1>
        <p class="text-gray-500 text-sm">Create and manage POS users (Super Admin only)</p>
      </div>
      <button
        @click="openModal()"
        class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-semibold hover:bg-purple-700 flex items-center gap-2 min-h-[44px]"
      >
        <i class="pi pi-plus text-xs"></i> Add User
      </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="p-4 border-b border-gray-100 flex flex-wrap gap-3">
        <input
          v-model="search"
          placeholder="Search by name or email..."
          class="flex-1 min-w-[220px] border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 min-h-[44px]"
        />
      </div>

      <div class="md:hidden p-3 space-y-3">
        <article v-for="u in filtered" :key="u.id" class="rounded-xl border border-gray-200 p-3 bg-white">
          <div class="flex items-start justify-between gap-2">
            <div>
              <p class="font-semibold text-gray-800">{{ u.name }}</p>
              <p class="text-xs text-gray-400">{{ u.email }}</p>
            </div>
            <span class="capitalize px-2 py-1 rounded-full text-xs font-semibold text-white" :style="{ background: roleColor(u.role) }">{{ u.role }}</span>
          </div>
          <div class="mt-2">
            <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="u.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">{{ u.is_active ? 'Active' : 'Inactive' }}</span>
          </div>
          <div class="mt-3 grid grid-cols-2 gap-2">
            <button @click="openModal(u)" class="min-h-[44px] rounded-lg border border-blue-200 text-blue-600 font-semibold">Edit</button>
            <button v-if="u.is_active" @click="deactivate(u)" class="min-h-[44px] rounded-lg border border-red-200 text-red-600 font-semibold">Deactivate</button>
          </div>
        </article>
      </div>

      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">User</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Role</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Status</th>
              <th class="text-center py-3 px-4 font-semibold text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="u in filtered" :key="u.id" class="hover:bg-gray-50 transition-colors">
              <td class="py-3 px-4">
                <div class="font-semibold text-gray-800">{{ u.name }}</div>
                <div class="text-xs text-gray-400">{{ u.email }}</div>
              </td>
              <td class="py-3 px-4">
                <span class="capitalize px-2 py-1 rounded-full text-xs font-semibold text-white" :style="{ background: roleColor(u.role) }">
                  {{ u.role }}
                </span>
              </td>
              <td class="py-3 px-4">
                <span
                  class="px-2 py-1 rounded-full text-xs font-semibold"
                  :class="u.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                >
                  {{ u.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                  <button @click="openModal(u)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                    <i class="pi pi-pencil text-xs"></i>
                  </button>
                  <button
                    v-if="u.is_active"
                    @click="deactivate(u)"
                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    title="Deactivate"
                  >
                    <i class="pi pi-trash text-xs"></i>
                  </button>
                  <span v-else class="text-xs text-gray-400">-</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-end md:items-center justify-center z-50 md:p-4">
      <div class="bg-white w-full h-[92vh] md:h-auto md:rounded-3xl md:max-w-2xl p-4 md:p-6 shadow-2xl max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-bold text-gray-800">{{ editUser ? 'Edit User' : 'Add User' }}</h2>
          <button @click="showModal = false" class="w-11 h-11 rounded-full hover:bg-gray-100 flex items-center justify-center">
            <i class="pi pi-times text-gray-500"></i>
          </button>
        </div>

        <form @submit.prevent="save" class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-20 md:pb-0">
          <div class="col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name *</label>
            <input v-model="form.name" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 min-h-[44px]" />
          </div>

          <div class="col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email *</label>
            <input v-model="form.email" type="email" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 min-h-[44px]" />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Role *</label>
            <select v-model="form.role" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300">
              <option value="super_admin">super_admin</option>
              <option value="admin">admin</option>
              <option value="cashier">cashier</option>
            </select>
          </div>

          <div class="flex items-end">
            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 cursor-pointer">
              <input type="checkbox" v-model="form.is_active" class="w-4 h-4 accent-purple-600" />
              Active
            </label>
          </div>

          <div class="col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
              Password <span class="text-xs text-gray-500">(required for new user)</span>
            </label>
            <input
              v-model="form.password"
              type="password"
              :placeholder="editUser ? 'Leave blank to keep current password' : 'Set password'"
              :required="!editUser"
              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300"
            />
          </div>

          <div class="col-span-2 flex gap-3 pt-2 sticky bottom-0 bg-white safe-bottom">
            <button type="button" @click="showModal = false" class="flex-1 py-3 border border-gray-200 rounded-xl text-gray-600 font-semibold hover:bg-gray-50 transition-colors">
              Cancel
            </button>
            <button type="submit" :disabled="saving" class="flex-1 touch-primary bg-purple-600 text-white rounded-xl font-semibold hover:bg-purple-700 disabled:opacity-60">
              {{ saving ? 'Saving...' : (editUser ? 'Update' : 'Create') }}
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

const users = ref<any[]>([])
const search = ref('')
const showModal = ref(false)
const saving = ref(false)
const editUser = ref<any | null>(null)

const form = ref({
  name: '',
  email: '',
  role: 'cashier' as 'super_admin' | 'admin' | 'cashier',
  is_active: true,
  password: '',
})

const filtered = computed(() => {
  const s = search.value.trim().toLowerCase()
  if (!s) return users.value
  return users.value.filter((u) => u.name.toLowerCase().includes(s) || u.email.toLowerCase().includes(s))
})

function roleColor(role: string) {
  if (role === 'super_admin') return '#7c3aed'
  if (role === 'admin') return '#f59e0b'
  return '#10b981'
}

function openModal(u: any | null = null) {
  editUser.value = u
  form.value.password = ''
  if (u) {
    form.value = {
      name: u.name ?? '',
      email: u.email ?? '',
      role: u.role ?? 'cashier',
      is_active: !!u.is_active,
      password: '',
    }
  } else {
    form.value = {
      name: '',
      email: '',
      role: 'cashier',
      is_active: true,
      password: '',
    }
  }
  showModal.value = true
}

async function load() {
  const { data } = await api.get('/users')
  users.value = data
}

async function deactivate(u: any) {
  if (!(await confirmAction(`Deactivate ${u.name}?`, 'Deactivate user'))) return
  await api.delete(`/users/${u.id}`)
  await load()
  await showSuccess('User deactivated successfully.')
}

async function save() {
  saving.value = true
  try {
    const payload: any = {
      name: form.value.name,
      email: form.value.email,
      role: form.value.role,
      is_active: form.value.is_active,
    }

    // Only send password when creating, or when updating with a new password.
    if (!editUser.value || (editUser.value && form.value.password)) {
      payload.password = form.value.password
    }

    if (editUser.value?.id) {
      await api.put(`/users/${editUser.value.id}`, payload)
    } else {
      await api.post('/users', payload)
    }

    showModal.value = false
    await load()
    await showSuccess(`User ${editUser.value?.id ? 'updated' : 'created'} successfully.`)
  } catch (e: any) {
    await showError(apiErrorMessage(e, 'Error saving user'))
  } finally {
    saving.value = false
  }
}

onMounted(load)

// Avoid stale password if user edits and then re-opens quickly.
watch(showModal, (v) => {
  if (!v) form.value.password = ''
})
</script>

