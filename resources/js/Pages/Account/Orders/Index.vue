<script setup>
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  orders: { type: Object, required: true } // paginator with links[]
})

function goTo(url) {
  if (!url) return
  const u = new URL(url, window.location.origin)
  const page = u.searchParams.get('page') || '1'
  router.get('/account/orders', { page }, { preserveScroll: true, preserveState: true })
}

function formatEUR(value, currency = 'EUR') {
  const n = Number(value ?? 0)
  try { return new Intl.NumberFormat('fr-BE', { style:'currency', currency }).format(n) }
  catch { return `${n.toFixed(2)} ${currency}` }
}
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-darknavy">Mes commandes</h1>
      <Link href="/dashboard" class="px-3 py-2 rounded-xl border hover:bg-gray-50">← Dashboard</Link>
    </div>

    <div class="bg-white rounded-2xl shadow overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="border-b bg-gray-50">
          <tr class="text-left text-gray-600">
            <th class="p-3">#</th>
            <th class="p-3">Date</th>
            <th class="p-3">Statut</th>
            <th class="p-3">Paiement</th>
            <th class="p-3">Total</th>
            <th class="p-3">Action</th>
          </tr>
        </thead>
        <tbody v-if="orders?.data?.length">
          <tr v-for="o in orders.data" :key="o.id" class="border-b">
            <td class="p-3 font-medium">#{{ o.id }}</td>
            <td class="p-3 whitespace-nowrap">{{ o.created_at }}</td>
            <td class="p-3">
              <span class="text-xs px-2 py-1 rounded-full bg-gray-100 ring-1 ring-gray-200 text-gray-800">
                {{ o.status }}
              </span>
            </td>
            <td class="p-3">
              <span class="text-xs px-2 py-1 rounded-full bg-gray-100 ring-1 ring-gray-200 text-gray-800">
                {{ o.payment_status }}
              </span>
            </td>
            <td class="p-3 font-semibold">{{ formatEUR(o.grand_total, o.currency) }}</td>
            <td class="p-3">
              <Link :href="`/checkout/${o.id}`" class="text-blue-700 hover:underline">Voir</Link>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td class="p-6 text-center text-gray-600" colspan="6">
              Aucune commande.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex items-center justify-end gap-2">
      <button
        v-for="link in orders.links"
        :key="link.label"
        class="px-3 py-1 rounded-xl border disabled:opacity-50"
        :class="link.active ? 'bg-darknavy text-white border-darknavy' : 'hover:bg-gray-50'"
        :disabled="!link.url"
        v-html="link.label"
        @click="goTo(link.url)"
      />
    </div>
  </div>
</template>
