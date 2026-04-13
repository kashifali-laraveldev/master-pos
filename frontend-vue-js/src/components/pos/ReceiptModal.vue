<template>
  <div class="fixed inset-0 bg-black/60 flex items-end sm:items-center justify-center z-[2000] sm:p-4">
    <div class="bg-white w-full h-[92vh] sm:h-auto sm:rounded-3xl sm:max-w-sm shadow-2xl flex flex-col">
      <div class="bg-gradient-to-r from-green-500 to-emerald-500 text-white px-6 py-5 rounded-t-3xl text-center">
        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
          <i class="pi pi-check text-white text-xl"></i>
        </div>
        <h3 class="font-bold text-lg">Sale Complete!</h3>
        <p class="text-green-100 text-sm">{{ sale.invoice_number }}</p>
      </div>

      <div class="flex-1 overflow-y-auto">
      <div id="receipt" class="px-5 py-4 mx-auto w-full max-w-[320px]">
        <div class="text-center mb-4 border-b border-dashed pb-4">
          <h2 class="font-bold text-lg">Master POS</h2>
          <p class="text-xs text-gray-500">{{ new Date(sale.sold_at).toLocaleString('ur-PK') }}</p>
          <p class="text-xs text-gray-500">Invoice: {{ sale.invoice_number }}</p>
          <p class="text-xs text-gray-500">Cashier: {{ sale.user?.name }}</p>
        </div>

        <table class="w-full text-xs mb-4">
          <thead>
            <tr class="border-b border-dashed">
              <th class="text-left py-1 text-gray-600 font-semibold">Item</th>
              <th class="text-center py-1 text-gray-600 font-semibold">Qty</th>
              <th class="text-right py-1 text-gray-600 font-semibold">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="item in sale.items"
              :key="item.id"
              class="border-b border-gray-100"
            >
              <td class="py-1.5 pr-2">
                <p class="font-medium text-gray-800">{{ item.product_name }}</p>
                <p class="text-gray-400">
                  ₨ {{ Number(item.price_per_unit).toLocaleString() }}/{{ item.unit_label }}
                </p>
              </td>
              <td class="py-1.5 text-center font-medium">
                {{ Number(item.quantity).toFixed(2) }} {{ item.unit_label }}
              </td>
              <td class="py-1.5 text-right font-bold">₨ {{ Number(item.total_price).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>

        <div class="border-t border-dashed pt-3 space-y-1">
          <div class="flex justify-between text-sm text-gray-600">
            <span>Subtotal</span>
            <span>₨ {{ Number(sale.subtotal).toLocaleString() }}</span>
          </div>
          <div v-if="sale.discount_amount > 0" class="flex justify-between text-sm text-red-500">
            <span>Discount</span>
            <span>- ₨ {{ Number(sale.discount_amount).toLocaleString() }}</span>
          </div>
          <div class="flex justify-between font-bold text-base border-t border-dashed pt-2 mt-2">
            <span>TOTAL</span>
            <span>₨ {{ Number(sale.total_amount).toLocaleString() }}</span>
          </div>
          <div class="flex justify-between text-sm text-gray-600">
            <span>Paid ({{ sale.payment_method }})</span>
            <span>₨ {{ Number(sale.amount_paid).toLocaleString() }}</span>
          </div>
          <div v-if="sale.change_amount > 0" class="flex justify-between text-sm font-semibold text-green-600">
            <span>Change</span>
            <span>₨ {{ Number(sale.change_amount).toLocaleString() }}</span>
          </div>
        </div>

        <div class="text-center mt-4 pt-3 border-t border-dashed">
          <p class="text-xs text-gray-400">Shukriya! Phir aana</p>
          <p class="text-xs text-gray-400">Thank you for your business</p>
        </div>
      </div>

      </div>

      <div class="px-4 pb-4 pt-3 flex flex-col sm:flex-row gap-2 border-t border-gray-100 safe-bottom no-print">
        <button
          @click="printReceipt"
          class="touch-primary flex items-center justify-center gap-2 border border-gray-200 rounded-2xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors"
        >
          <i class="pi pi-print"></i> Print
        </button>
        <button
          @click="shareReceipt"
          class="touch-primary flex items-center justify-center gap-2 border border-gray-200 rounded-2xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors"
        >
          <i class="pi pi-share-alt"></i> Share / PDF
        </button>
        <button
          @click="$emit('close')"
          class="touch-primary bg-purple-600 text-white rounded-2xl font-semibold hover:bg-purple-700 transition-colors flex items-center justify-center gap-2"
        >
          <i class="pi pi-plus"></i> New Sale
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { showSuccess } from '../../lib/swal'

const props = defineProps<{ sale: any }>()
defineEmits<{ (e: 'close'): void }>()

function printReceipt() {
  window.print()
}

async function shareReceipt() {
  const text = `Master POS Receipt\nInvoice: ${props.sale.invoice_number}\nTotal: Rs ${Number(props.sale.total_amount).toLocaleString()}`
  if (navigator.share) {
    await navigator.share({ title: 'Master POS Receipt', text })
    return
  }
  await navigator.clipboard.writeText(text)
  await showSuccess('Receipt summary copied. Use Print to save as PDF.', 'Copied')
}
</script>

