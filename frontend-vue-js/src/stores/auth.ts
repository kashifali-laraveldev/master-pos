import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import api from '../lib/axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<any>(JSON.parse(localStorage.getItem('pos_user') || 'null'))
  const token = ref<string>(localStorage.getItem('pos_token') || '')

  const isLoggedIn = computed(() => !!token.value)
  const isAdmin = computed(() => ['admin', 'super_admin'].includes(user.value?.role))
  const isSuperAdmin = computed(() => user.value?.role === 'super_admin')

  async function login(email: string, password: string) {
    const { data } = await api.post('/auth/login', { email, password })
    user.value = data.user
    token.value = data.token
    localStorage.setItem('pos_token', data.token)
    localStorage.setItem('pos_user', JSON.stringify(data.user))
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } catch {
      // ignore
    }

    user.value = null
    token.value = ''
    localStorage.removeItem('pos_token')
    localStorage.removeItem('pos_user')
  }

  return { user, token, isLoggedIn, isAdmin, isSuperAdmin, login, logout }
})

