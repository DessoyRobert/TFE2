<script setup>
import { router, usePage } from '@inertiajs/vue3'
const props = defineProps({ orders: Object, filters: Object })

function updateStatus(id, status) {
  router.patch(route('admin.orders.updateStatus', id), { status })
}
</script>

<template>
  <div class="max-w-6xl mx-auto p-4 space-y-4">
    <h1 class="text-2xl font-semibold">Commandes</h1>

    <div class="bg-white rounded-2xl shadow overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="border-b">
          <tr class="text-left">
            <th class="p-3">#</th>
            <th class="p-3">Date</th>
            <th class="p-3">Client</th>
            <th class="p-3">Email</th>
            <th class="p-3">Montant</th>
            <th class="p-3">Statut</th>
            <th class="p-3">Paiement</th>
            <th class="p-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="o in props.orders.data" :key="o.id" class="border-b">
            <td class="p-3">{{ o.id }}</td>
            <td class="p-3 whitespace-nowrap">{{ o.created_at }}</td>
            <td class="p-3">{{ o.customer }}</td>
            <td class="p-3">{{ o.email }}</td>
            <td class="p-3">{{ o.grand_total }} {{ o.currency }}</td>
            <td class="p-3">{{ o.status }}</td>
            <td class="p-3">{{ o.payment_status }}</td>
            <td class="p-3">
              <select class="border p-1 rounded" @change="e => updateStatus(o.id, e.target.value)">
                <option disabled selected>Changer...</option>
                <option value="pending">pending</option>
                <option value="paid">paid</option>
                <option value="preparing">preparing</option>
                <option value="shipped">shipped</option>
                <option value="delivered">delivered</option>
                <option value="canceled">canceled</option>
                <option value="refunded">refunded</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination basique -->
    <div class="flex gap-2">
      <a v-if="props.orders.prev_page_url" :href="props.orders.prev_page_url" class="px-3 py-1 rounded bg-gray-100">Préc.</a>
      <a v-if="props.orders.next_page_url" :href="props.orders.next_page_url" class="px-3 py-1 rounded bg-gray-100">Suiv.</a>
    </div>
  </div>
</template>
