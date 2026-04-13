<template>
  <div class="flex h-screen bg-gray-100 overflow-hidden">
    <div
      v-if="sidebarOpen && (isTablet || isPosRoute)"
      class="fixed inset-0 bg-black/40 z-30"
      @click="sidebarOpen = false"
    ></div>

    <!-- Sidebar (desktop / tablet only) -->
    <aside
      v-if="!isPosRoute || sidebarOpen"
      :class="[
        'bg-gradient-to-b from-purple-900 to-purple-800 text-white flex-col transition-all duration-300 shadow-xl z-[120]',
        isPosRoute ? 'fixed left-0 w-64 flex top-14 bottom-0' : 'hidden md:flex',
        isPosRoute ? '' : (isTablet ? 'w-20' : 'w-64'),
        isPosRoute ? '' : (isTablet ? 'fixed inset-y-0 left-0' : 'relative'),
        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
      ]"
    >
      <!-- Brand -->
      <div class="p-5 border-b border-purple-700">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-yellow-400 rounded-xl flex items-center justify-center">
            <span class="text-purple-900 font-bold text-lg">M</span>
          </div>
          <div v-if="!isTablet">
            <h1 class="font-bold text-lg leading-tight">Master POS</h1>
            <p class="text-purple-300 text-xs">POS System</p>
          </div>
        </div>
      </div>

      <!-- Nav -->
      <nav class="flex-1 py-4 overflow-y-auto">
        <NavItem to="/pos" icon="pi pi-shopping-cart" label="POS Billing" :collapsed="!isPosRoute && isTablet" />

        <template v-if="auth.isAdmin">
          <div v-if="!isTablet" class="px-4 py-2 mt-2 text-xs font-semibold text-purple-400 uppercase tracking-wider">Management</div>
          <NavItem to="/dashboard" icon="pi pi-chart-bar" label="Dashboard" :collapsed="!isPosRoute && isTablet" />
          <NavItem to="/products" icon="pi pi-box" label="Products" :collapsed="!isPosRoute && isTablet" />
          <NavItem to="/categories" icon="pi pi-tag" label="Categories" :collapsed="!isPosRoute && isTablet" />
          <NavItem to="/inventory" icon="pi pi-warehouse" label="Inventory" :collapsed="!isPosRoute && isTablet" />
          <NavItem to="/sales" icon="pi pi-receipt" label="Sales History" :collapsed="!isPosRoute && isTablet" />
          <NavItem to="/reports" icon="pi pi-file-export" label="Reports" :collapsed="!isPosRoute && isTablet" />
        </template>

        <template v-if="auth.isSuperAdmin">
          <div v-if="!isTablet" class="px-4 py-2 mt-2 text-xs font-semibold text-purple-400 uppercase tracking-wider">System</div>
          <NavItem to="/users" icon="pi pi-users" label="Users" :collapsed="!isPosRoute && isTablet" />
        </template>
      </nav>

      <!-- User -->
      <div class="p-4 border-t border-purple-700" v-if="!isTablet">
        <div class="flex items-center gap-3 mb-3">
          <div
            class="w-8 h-8 rounded-full bg-yellow-400 flex items-center justify-center text-purple-900 font-bold text-sm"
          >
            {{ auth.user?.name?.charAt(0) }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium truncate">{{ auth.user?.name }}</p>
            <p class="text-xs text-purple-300 capitalize">{{ auth.user?.role?.replace('_', ' ') }}</p>
          </div>
        </div>

        <button
          @click="handleLogout"
          class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-purple-300 hover:bg-purple-700 hover:text-white transition-colors"
        >
          <i class="pi pi-sign-out text-xs"></i> Logout
        </button>
      </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col overflow-hidden pb-16 md:pb-0">
      <!-- Topbar -->
      <header class="sticky top-0 z-40 bg-white border-b border-gray-200/90 px-2 sm:px-6 h-14 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-1.5 min-w-0 flex-1">
          <button
            v-if="isPosRoute && auth.isSuperAdmin"
            @click="goDashboard"
            class="px-2 sm:px-3 min-h-[36px] rounded-lg bg-purple-600 text-white text-xs sm:text-sm font-semibold hover:bg-purple-700 transition-colors inline-flex items-center gap-1.5 shrink-0"
          >
            <i class="pi pi-home text-xs"></i>
            <span class="hidden min-[400px]:inline">Dashboard</span>
          </button>
          <button
            v-else-if="showBurgerMenu && (isPosRoute || !isMobile)"
            @click="sidebarOpen = !sidebarOpen"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors min-w-[40px] min-h-[40px] flex shrink-0"
          >
            <i class="pi pi-bars text-gray-600"></i>
          </button>
          <div class="min-w-0">
            <p class="text-[15px] sm:text-[16px] font-bold text-gray-800 leading-tight truncate max-w-[90px] sm:max-w-none">Master POS</p>
            <p class="text-[10px] sm:text-[11px] text-gray-500 leading-tight truncate max-w-[90px] sm:max-w-none">POS System</p>
          </div>
        </div>
        <div class="flex items-center gap-1.5 sm:gap-3 ml-1">
          <template v-if="isPosRoute">
            <div class="hidden md:flex items-center gap-2 px-2 py-1 rounded-lg bg-purple-50 border border-purple-100">
              <i class="pi pi-calendar text-[11px] text-purple-500"></i>
              <span class="text-[12px] text-gray-500">{{ currentDate }}</span>
            </div>
            <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-gray-50 border border-gray-200 min-w-0 max-w-[120px] sm:max-w-none">
              <span class="text-[11px] sm:text-[12px] text-gray-700 truncate max-w-[70px] sm:max-w-[96px] font-medium">{{ auth.user?.name }}</span>
              <span class="hidden sm:inline text-[10px] capitalize text-gray-500">{{ auth.user?.role?.replace('_', ' ') }}</span>
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="handleLogout"
                class="min-h-[36px] px-2 sm:px-3 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 inline-flex items-center gap-1.5 shrink-0"
                title="Logout"
              >
                <i class="pi pi-sign-out"></i>
                <span class="hidden sm:inline text-[12px] font-medium">Log out</span>
              </button>
            </div>
          </template>
          <template v-else>
          <div
            v-if="lowStockCount > 0"
            class="hidden sm:flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-700 px-3 py-1.5 rounded-lg text-sm"
          >
            <i class="pi pi-exclamation-triangle text-xs"></i>
            {{ lowStockCount }} low stock items
          </div>
          <span class="text-[12px] text-gray-500">{{ currentDate }}</span>
          </template>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 overflow-auto">
        <router-view />
      </main>
    </div>

    <!-- Mobile bottom nav -->
    <nav
      v-if="isMobile && mobileTabs.length"
      class="mobile-bottom-nav fixed bottom-0 left-0 right-0 h-16 bg-white border-t border-gray-200 z-[100] safe-bottom"
    >
      <div
        class="h-full"
        :class="mobileTabs.length === 5 ? 'grid grid-cols-5' : mobileTabs.length === 3 ? 'grid grid-cols-3' : mobileTabs.length === 2 ? 'grid grid-cols-2' : 'grid grid-cols-1'"
      >
        <button
          v-for="item in mobileTabs"
          :key="item.to"
          @click="goTab(item.to)"
          class="flex flex-col items-center justify-center gap-0.5 min-h-[48px]"
          :class="route.path === item.to ? 'text-purple-600' : 'text-gray-500'"
        >
          <i :class="[item.icon, 'text-[24px] leading-none']"></i>
          <span class="text-[10px] leading-none">{{ item.label }}</span>
        </button>
      </div>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../lib/axios'
import NavItem from '../components/NavItem.vue'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const sidebarOpen = ref(true)
const lowStockCount = ref(0)
const isMobile = ref(false)
const isTablet = ref(false)
const defaultAccounts = ['demo@masterpos.com']
const isPosRoute = computed(() => route.path === '/pos')
const showBurgerMenu = computed(() => auth.user?.role !== 'cashier')
const mobileTabs = computed(() => {
  if (isPosRoute.value) {
    if (auth.isAdmin || auth.isSuperAdmin) {
      return [
        { to: '/pos', icon: 'pi pi-shopping-cart', label: 'POS' },
        { to: '/dashboard', icon: 'pi pi-home', label: 'Admin' },
      ]
    }
    return [{ to: '/pos', icon: 'pi pi-shopping-cart', label: 'POS' }]
  }

  if (auth.isAdmin || auth.isSuperAdmin) {
    return [
      { to: '/pos', icon: 'pi pi-shopping-cart', label: 'POS' },
      { to: '/sales', icon: 'pi pi-receipt', label: 'Orders' },
      { to: '/dashboard', icon: 'pi pi-home', label: 'Home' },
      { to: '/products', icon: 'pi pi-box', label: 'Products' },
      { to: '/users', icon: 'pi pi-cog', label: 'Settings' },
    ]
  }
  if (auth.user?.role === 'cashier') {
    return [
      { to: '/pos', icon: 'pi pi-shopping-cart', label: 'POS' },
      { to: '/sales', icon: 'pi pi-receipt', label: 'Orders' },
      { to: '/dashboard', icon: 'pi pi-home', label: 'Home' },
    ]
  }
  return []
})

const isCustomUser = computed(() => {
  const email = auth.user?.email?.toLowerCase?.() || ''
  return !!email && !defaultAccounts.includes(email)
})

const currentDate = computed(() =>
  new Date().toLocaleDateString('ur-PK', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }),
)

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}

function updateViewport() {
  isMobile.value = window.innerWidth < 768
  isTablet.value = window.innerWidth >= 768 && window.innerWidth < 1024
  // Keep desktop always visible unless custom-user minimized preference applies.
  if (!isPosRoute.value && !isMobile.value && !isCustomUser.value) {
    sidebarOpen.value = true
  }
}

function goTab(path: string) {
  if (route.path !== path) router.push(path)
}

function goDashboard() {
  router.push('/dashboard')
}

onMounted(async () => {
  updateViewport()
  window.addEventListener('resize', updateViewport)

  // POS should start in full-width mode; open only via burger.
  if (isPosRoute.value) {
    sidebarOpen.value = false
  }

  // Custom users start with minimized sidebar by default.
  if (isCustomUser.value && !isPosRoute.value) {
    sidebarOpen.value = false
  }

  try {
    const { data } = await api.get('/dashboard')
    lowStockCount.value = data.low_stock_count
  } catch {
    // ignore
  }
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateViewport)
})

watch(
  () => auth.user?.email,
  () => {
    if (isCustomUser.value) {
      sidebarOpen.value = false
    }
  },
)

watch(
  () => route.fullPath,
  () => {
    if (isMobile.value || isTablet.value || isPosRoute.value) {
      sidebarOpen.value = false
    }
  },
)
</script>

