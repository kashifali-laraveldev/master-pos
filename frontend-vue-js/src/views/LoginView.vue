<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-900 via-purple-800 to-indigo-900"
  >
    <div class="bg-white rounded-3xl p-10 w-full max-w-md shadow-2xl">
      <div class="text-center mb-8">
        <div
          class="inline-flex w-16 h-16 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-2xl items-center justify-center mb-4 shadow-lg"
        >
          <span class="text-purple-900 font-bold text-3xl">M</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">Master POS</h1>
        <p class="text-gray-500 text-sm mt-1">Point of Sale System</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-5">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
          <div class="relative">
            <i class="pi pi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="demo@masterpos.com"
              class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
          <div class="relative">
            <i class="pi pi-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input
              v-model="form.password"
              :type="showPass ? 'text' : 'password'"
              required
              placeholder="••••••••"
              class="w-full pl-10 pr-12 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
            />
            <button
              type="button"
              @click="showPass = !showPass"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <i :class="showPass ? 'pi pi-eye-slash' : 'pi pi-eye'"></i>
            </button>
          </div>
        </div>

        <div
          v-if="error"
          class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2"
        >
          <i class="pi pi-exclamation-circle"></i>
          {{ error }}
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold py-3 rounded-xl transition-all shadow-lg shadow-purple-200 disabled:opacity-60 flex items-center justify-center gap-2"
        >
          <i v-if="loading" class="pi pi-spin pi-spinner"></i>
          <i v-else class="pi pi-sign-in"></i>
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>
      </form>

      <p class="text-center text-xs text-gray-400 mt-6">
        Default:
        <code class="bg-gray-100 px-1 rounded">demo@masterpos.com</code> /
        <code class="bg-gray-100 px-1 rounded">demo1234</code>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { apiErrorMessage, showError, showSuccess } from '../lib/swal'

const auth = useAuthStore()
const router = useRouter()

const form = ref({ email: '', password: '' })
const loading = ref(false)
const error = ref('')
const showPass = ref(false)

async function handleLogin() {
  loading.value = true
  error.value = ''
  try {
    await auth.login(form.value.email, form.value.password)
    await showSuccess('Welcome to Master POS!')
    router.push('/pos')
  } catch (e: any) {
    error.value = apiErrorMessage(e, 'Login failed')
    await showError(error.value, 'Login failed')
  } finally {
    loading.value = false
  }
}
</script>

