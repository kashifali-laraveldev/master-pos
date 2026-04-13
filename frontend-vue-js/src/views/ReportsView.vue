<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Reports</h1>
        <p class="text-gray-500 text-sm">Revenue, top products & payment mix</p>
      </div>
      <div class="flex items-center gap-3">
        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Last</label>
        <select
          v-model.number="days"
          class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300"
        >
          <option :value="7">7 days</option>
          <option :value="14">14 days</option>
          <option :value="21">21 days</option>
          <option :value="30">30 days</option>
        </select>
        <button
          @click="load"
          class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-semibold hover:bg-purple-700"
        >
          Refresh
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Today's Revenue</p>
        <p class="text-2xl font-bold text-gray-800 mt-1">₨ {{ format(stats.today?.revenue ?? 0) }}</p>
        <p class="text-sm text-gray-500 mt-1">{{ stats.today?.sales_count ?? 0 }} sales</p>
      </div>
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">This Month Revenue</p>
        <p class="text-2xl font-bold text-gray-800 mt-1">₨ {{ format(stats.month?.revenue ?? 0) }}</p>
        <p class="text-sm text-gray-500 mt-1">{{ stats.month?.sales_count ?? 0 }} sales</p>
      </div>
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Low Stock Items</p>
        <p class="text-2xl font-bold text-gray-800 mt-1">{{ stats.low_stock_count ?? 0 }}</p>
      </div>
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Payment Mix</p>
        <p class="text-sm text-gray-600 mt-2" v-if="paymentBreakdown.length === 0">No data yet</p>
        <div v-else class="text-sm text-gray-600 mt-2 space-y-1">
          <div v-for="p in paymentBreakdown" :key="p.payment_method" class="flex justify-between gap-3">
            <span class="font-semibold">{{ p.payment_method }}</span>
            <span class="text-gray-500">{{ p.count }} sales</span>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="lg:col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 mb-4">Revenue - Last {{ days }} Days</h3>
        <Bar v-if="chartData" :data="chartData" :options="chartOptions" />
        <div v-else class="text-gray-500 text-sm">No chart data</div>
      </div>

      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 mb-4">Top Products (This Month)</h3>
        <div class="space-y-3">
          <div v-for="(p, idx) in stats.top_products ?? []" :key="p.product_id" class="flex items-center gap-3">
            <span
              class="w-6 h-6 rounded-full text-white text-xs font-bold flex items-center justify-center flex-shrink-0"
              :class="[idx === 0 ? 'bg-purple-600' : idx === 1 ? 'bg-indigo-500' : idx === 2 ? 'bg-blue-500' : 'bg-teal-500']"
            >
              {{ idx + 1 }}
            </span>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-semibold text-gray-800 truncate">{{ p.product_name }}</div>
              <div class="text-xs text-gray-500">
                {{ Number(p.total_qty).toFixed(1) }} {{ p.unit_label }}
              </div>
            </div>
            <div class="text-sm font-bold text-purple-700">₨ {{ format(p.total_revenue) }}</div>
          </div>
        </div>
        <div v-if="(stats.top_products ?? []).length === 0" class="text-gray-500 text-sm mt-3">No data yet</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import api from '../lib/axios'
import { Bar } from 'vue-chartjs'

import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const days = ref<number>(14)
const stats = ref<any>({})

const paymentBreakdown = computed(() => {
  const list = stats.value?.payment_breakdown ?? []
  return list
})

const chartData = computed(() => {
  const daily = stats.value?.daily_chart
  if (!Array.isArray(daily) || daily.length === 0) return null

  return {
    labels: daily.map((d: any) => new Date(d.date).toLocaleDateString('en', { day: '2-digit', month: 'short' })),
    datasets: [
      {
        label: 'Revenue (₨)',
        data: daily.map((d: any) => Number(d.revenue)),
        backgroundColor: '#7c3aed',
        borderRadius: 8,
      },
    ],
  }
})

const chartOptions = {
  responsive: true,
  plugins: { legend: { display: false } },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: (v: any) => '₨' + Number(v).toLocaleString('en-PK'),
      },
    },
  },
}

function format(n: any) {
  return Number(n || 0).toLocaleString('en-PK', { maximumFractionDigits: 0 })
}

async function load() {
  const { data } = await api.get('/reports', { params: { days: days.value } })
  stats.value = data
}

onMounted(load)
</script>

