<template>
  <div class="p-4 sm:p-6">
    <div class="flex flex-col gap-3 mb-6 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-gray-500 text-sm">Business analytics and live insights</p>
      </div>
      <div class="w-full lg:w-auto overflow-x-auto">
        <div class="flex items-center gap-2 min-w-max">
        <button
          @click="chartMode = 'revenue'"
          :class="[
            'px-3 py-2 rounded-xl text-sm font-semibold border min-h-[44px] whitespace-nowrap',
            chartMode === 'revenue' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-700 border-gray-200',
          ]"
        >
          Revenue
        </button>
        <button
          @click="chartMode = 'sales'"
          :class="[
            'px-3 py-2 rounded-xl text-sm font-semibold border min-h-[44px] whitespace-nowrap',
            chartMode === 'sales' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-700 border-gray-200',
          ]"
        >
          Sales Count
        </button>
        <button @click="load" class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-semibold hover:bg-purple-700 min-h-[44px] whitespace-nowrap">
          Refresh
        </button>
      </div>
      </div>
    </div>

    <div v-if="loading" class="text-gray-500">Loading dashboard...</div>

    <template v-else>
      <!-- KPI cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Today's Revenue</p>
          <p class="text-2xl font-bold text-gray-800 mt-1">₨ {{ format(stats.today?.revenue ?? 0) }}</p>
          <p class="text-xs mt-2" :class="growthClass(stats.today?.growth_percent)">
            {{ growthLabel(stats.today?.growth_percent, 'vs yesterday') }}
          </p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Today's Sales</p>
          <p class="text-2xl font-bold text-gray-800 mt-1">{{ stats.today?.sales_count ?? 0 }}</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">This Month Revenue</p>
          <p class="text-2xl font-bold text-gray-800 mt-1">₨ {{ format(stats.month?.revenue ?? 0) }}</p>
          <p class="text-xs mt-2" :class="growthClass(stats.month?.growth_percent)">
            {{ growthLabel(stats.month?.growth_percent, 'vs last month') }}
          </p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Avg Ticket</p>
          <p class="text-2xl font-bold text-gray-800 mt-1">₨ {{ format(stats.avg_ticket ?? 0) }}</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Low Stock Items</p>
          <p class="text-2xl font-bold text-gray-800 mt-1">{{ stats.low_stock_count ?? 0 }}</p>
          <p class="text-xs text-gray-500 mt-2">{{ stats.total_products ?? 0 }} active products</p>
        </div>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-6">
        <!-- Trend chart -->
        <div class="xl:col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <h3 class="font-bold text-gray-800 mb-4">Last 14 Days Trend</h3>
          <Bar v-if="trendChartData" :data="trendChartData" :options="trendChartOptions" class="max-h-[320px]" />
          <p v-else class="text-sm text-gray-500">No trend data available.</p>
        </div>

        <!-- Payment mix -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <h3 class="font-bold text-gray-800 mb-4">Payment Mix (30 Days)</h3>
          <Doughnut
            v-if="paymentChartData && paymentChartData.labels.length"
            :data="paymentChartData"
            :options="paymentChartOptions"
            class="max-h-[260px]"
          />
          <p v-else class="text-sm text-gray-500">No payment data available.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <!-- Top products -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <h3 class="font-bold text-gray-800 mb-4">Top Products (This Month)</h3>
          <div v-if="(stats.top_products ?? []).length" class="space-y-3">
            <div v-for="(p, idx) in stats.top_products" :key="p.product_id" class="flex items-center gap-3">
              <span class="w-6 h-6 rounded-full text-white text-xs font-bold flex items-center justify-center"
                :class="rankClass(idx)">{{ idx + 1 }}</span>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ p.product_name }}</p>
                <p class="text-xs text-gray-500">{{ Number(p.total_qty).toFixed(1) }} {{ p.unit_label }}</p>
              </div>
              <p class="text-sm font-bold text-purple-700">₨ {{ format(p.total_revenue) }}</p>
            </div>
          </div>
          <p v-else class="text-sm text-gray-500">No top products yet.</p>
        </div>

        <!-- Low stock products -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <h3 class="font-bold text-gray-800 mb-4">Critical Low Stock</h3>
          <div v-if="(stats.low_stock_products ?? []).length" class="space-y-3">
            <div v-for="p in stats.low_stock_products" :key="p.id" class="flex items-center justify-between gap-3">
              <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ p.name }}</p>
                <p class="text-xs text-gray-500">
                  Alert: {{ Number(p.low_stock_alert).toFixed(1) }} {{ p.unit_label }}
                </p>
              </div>
              <p class="text-sm font-bold text-red-600">
                {{ Number(p.stock_quantity).toFixed(1) }} {{ p.unit_label }}
              </p>
            </div>
          </div>
          <p v-else class="text-sm text-gray-500">No low stock items.</p>
        </div>

        <!-- Recent sales -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <h3 class="font-bold text-gray-800 mb-4">Recent Sales</h3>
          <div v-if="(stats.recent_sales ?? []).length" class="space-y-3">
            <div v-for="s in stats.recent_sales" :key="s.id" class="flex items-center justify-between gap-3">
              <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ s.invoice_number }}</p>
                <p class="text-xs text-gray-500">
                  {{ s.user?.name }} - {{ s.payment_method }} - {{ formatDateTime(s.sold_at) }}
                </p>
              </div>
              <p class="text-sm font-bold text-green-700">₨ {{ format(s.total_amount) }}</p>
            </div>
          </div>
          <p v-else class="text-sm text-gray-500">No recent sales.</p>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import api from '../lib/axios'
import { Bar, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement)

const stats = ref<any>({})
const loading = ref(false)
const chartMode = ref<'revenue' | 'sales'>('revenue')

const trendChartData = computed(() => {
  const rows = stats.value?.daily_chart || []
  if (!rows.length) return null

  return {
    labels: rows.map((r: any) => new Date(r.date).toLocaleDateString('en-PK', { day: '2-digit', month: 'short' })),
    datasets: [
      {
        label: chartMode.value === 'revenue' ? 'Revenue (₨)' : 'Sales Count',
        data: rows.map((r: any) => (chartMode.value === 'revenue' ? Number(r.revenue || 0) : Number(r.count || 0))),
        backgroundColor: chartMode.value === 'revenue' ? '#7c3aed' : '#2563eb',
        borderRadius: 8,
      },
    ],
  }
})

const trendChartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: (v: any) =>
          chartMode.value === 'revenue' ? `₨${Number(v).toLocaleString('en-PK')}` : Number(v).toLocaleString('en-PK'),
      },
    },
  },
}))

const paymentChartData = computed(() => {
  const rows = stats.value?.payment_breakdown || []
  if (!rows.length) return null

  return {
    labels: rows.map((r: any) => r.payment_method),
    datasets: [
      {
        data: rows.map((r: any) => Number(r.total_revenue || 0)),
        backgroundColor: ['#7c3aed', '#2563eb', '#f59e0b', '#10b981', '#ef4444'],
        borderWidth: 0,
      },
    ],
  }
})

const paymentChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom' as const },
    tooltip: {
      callbacks: {
        label: (ctx: any) => `${ctx.label}: ₨${Number(ctx.parsed).toLocaleString('en-PK')}`,
      },
    },
  },
}

function format(n: any) {
  return Number(n || 0).toLocaleString('en-PK', { maximumFractionDigits: 0 })
}

function formatDateTime(v: string) {
  return new Date(v).toLocaleString('en-PK', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })
}

function rankClass(i: number) {
  return i === 0 ? 'bg-purple-600' : i === 1 ? 'bg-indigo-500' : i === 2 ? 'bg-blue-500' : 'bg-teal-500'
}

function growthClass(value: number | null | undefined) {
  if (value === null || value === undefined) return 'text-gray-500'
  if (value >= 0) return 'text-green-600'
  return 'text-red-600'
}

function growthLabel(value: number | null | undefined, suffix: string) {
  if (value === null || value === undefined) return `No prior data (${suffix})`
  const sign = value > 0 ? '+' : ''
  return `${sign}${Number(value).toFixed(2)}% ${suffix}`
}

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/dashboard')
    stats.value = data
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

