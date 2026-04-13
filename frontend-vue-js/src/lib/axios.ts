import axios from 'axios'
import { hideLoader, showError, showLoader } from './swal'

const rawBaseUrl = import.meta.env.VITE_API_URL || import.meta.env.VITE_API_BASE_URL || '/api'
const API_BASE_URL = rawBaseUrl.replace(/\/+$/, '')

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  withCredentials: true,
})

api.interceptors.request.use((cfg) => {
  showLoader('Loading...')
  const tenantId = localStorage.getItem('pos_tenant_id') || import.meta.env.VITE_TENANT_ID || 'demo-tenant'
  if (tenantId) cfg.headers['X-Tenant-Id'] = tenantId
  const token = localStorage.getItem('pos_token')
  if (token) cfg.headers.Authorization = `Bearer ${token}`
  return cfg
})

api.interceptors.response.use(
  (res) => {
    hideLoader()
    return res
  },
  (err) => {
    hideLoader()
    if (err.response?.status === 401) {
      void showError('Session expired. Please login again.', 'Unauthorized')
      localStorage.removeItem('pos_token')
      localStorage.removeItem('pos_user')
      window.location.href = '/login'
    }
    return Promise.reject(err)
  },
)

export const API_ORIGIN = API_BASE_URL.replace(/\/api$/, '')
export default api

