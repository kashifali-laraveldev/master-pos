import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  { path: '/login', component: () => import('../views/LoginView.vue'), meta: { public: true } },
  {
    path: '/',
    component: () => import('../layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: '/pos' },
      { path: 'pos', component: () => import('../views/PosView.vue'), name: 'pos' },
      { path: 'dashboard', component: () => import('../views/DashboardView.vue'), name: 'dashboard', meta: { adminOnly: true } },
      { path: 'products', component: () => import('../views/ProductsView.vue'), name: 'products', meta: { adminOnly: true } },
      { path: 'categories', component: () => import('../views/CategoriesView.vue'), name: 'categories', meta: { adminOnly: true } },
      { path: 'inventory', component: () => import('../views/InventoryView.vue'), name: 'inventory', meta: { adminOnly: true } },
      { path: 'sales', component: () => import('../views/SalesView.vue'), name: 'sales', meta: { adminOnly: true } },
      { path: 'reports', component: () => import('../views/ReportsView.vue'), name: 'reports', meta: { adminOnly: true } },
      { path: 'users', component: () => import('../views/UsersView.vue'), name: 'users', meta: { superAdminOnly: true } },
    ],
  },
  { path: '/:pathMatch(.*)*', component: () => import('../views/NotFoundView.vue'), meta: { public: true } },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach((to, _from, next) => {
  const auth = useAuthStore()

  if ((to.meta as any).public) return next()
  if (!auth.isLoggedIn) return next('/login')

  if ((to.meta as any).superAdminOnly && !auth.isSuperAdmin) return next('/pos')
  if ((to.meta as any).adminOnly && !auth.isAdmin) return next('/pos')

  next()
})

router.onError((error) => {
  console.error('Router navigation error:', error)
})

export default router

