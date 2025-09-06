<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  order: {
    type: Object,
    required: true,
    // attendu côté controller:
    // {
    //   id, created_at, status, payment_status, currency, customer, email,
    //   amounts: { subtotal, shipping, tax, discount, grand },
    //   items: [{ id, type, ref_id, name, quantity, unit, total }],
    //   payments: [{ id, method, amount, status, created_at }]
    // }
  }
})

const selectedStatus = ref(props.order.status)

const statusList = [
  'pending', 'paid', 'preparing', 'shipped', 'delivered', 'canceled', 'refunded'
]

function badgeClass (status) {
  switch (status) {
    case 'paid': return 'bg-green-100 text-green-800 ring-1 ring-green-200'
    case 'preparing': return 'bg-amber-100 text-amber-800 ring-1 ring-amber-200'
    case 'shipped': return 'bg-blue-100 text-blue-800 ring-1 ring-blue-200'
    case 'delivered': return 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200'
    case 'canceled': return 'bg-rose-100 text-rose-800 ring-1 ring-rose-200'
    case 'refunded': return 'bg-purple-100 text-purple-800 ring-1 ring-purple-200'
    default: return 'bg-gray-100 text-gray-800 ring-1 ring-gray-200'
  }
}

const totalLines = computed(() => [
  { label: 'Sous-total', value: props.order.amounts?.subtotal },
  { label: 'Livraison',  value: props.order.amounts?.shipping },
  { label: 'TVA',        value: props.order.amounts?.tax },
  ...(Number(props.order.amounts?.discount) ? [{ label: 'Réduction', value: `-${props.order.amounts.discount}` }] : []),
  { label: 'Total',      value: props.order.amounts?.grand, emphasize: true }
])

function updateStatus () {
  if (!selectedStatus.value || selectedStatus.value === props.order.status) return
  router.patch(route('admin.orders.updateStatus', props.order.id), { status: selectedStatus.value }, {
    preserveScroll: true,
    onSuccess: () => {},
  })
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Commande #{{ order.id }}</h1>
        <p class="text-gray-600">Créée le {{ order.created_at }}</p>
      </div>
      <Link
        href="/admin/orders"
        class="px-4 py-2 rounded-xl border hover:bg-gray-50"
      >← Toutes les commandes</Link>
    </div>

    <!-- En-têtes -->
    <div class="grid md:grid-cols-3 gap-6">
      <div class="bg-white rounded-2xl shadow p-5 space-y-3">
        <h2 class="font-semibold text-darknavy">Client</h2>
        <div class="text-sm">
          <div class="font-medium">{{ order.customer }}</div>
          <div class="text-gray-600">{{ order.email }}</div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow p-5 space-y-3">
        <h2 class="font-semibold text-darknavy">Statuts</h2>
        <div class="flex flex-wrap gap-2 items-center">
          <span class="text-xs px-2 py-1 rounded-full" :class="badgeClass(order.status)">
            {{ order.status }}
          </span>
          <span class="text-xs px-2 py-1 rounded-full" :class="badgeClass(order.payment_status)">
            paiement: {{ order.payment_status }}
          </span>
        </div>

        <div class="flex items-center gap-2 mt-3">
          <select v-model="selectedStatus" class="border rounded-lg px-3 py-2">
            <option v-for="s in statusList" :key="s" :value="s">{{ s }}</option>
          </select>
          <button
            class="px-4 py-2 rounded-xl bg-darknavy text-white hover:bg-primary"
            @click="updateStatus"
          >
            Mettre à jour
          </button>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow p-5 space-y-2">
        <h2 class="font-semibold text-darknavy">Montants ({{ order.currency }})</h2>
        <ul class="text-sm">
          <li
            v-for="(l, i) in totalLines"
            :key="i"
            class="flex items-center justify-between"
            :class="l.emphasize ? 'mt-2 text-base font-semibold' : ''"
          >
            <span class="text-gray-600">{{ l.label }}</span>
            <span>{{ l.value }}</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Items -->
    <div class="bg-white rounded-2xl shadow p-5">
      <h2 class="font-semibold text-darknavy mb-3">Articles</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-gray-600">
              <th class="py-2 pr-4">Type</th>
              <th class="py-2 pr-4">Référence</th>
              <th class="py-2 pr-4">Nom</th>
              <th class="py-2 pr-4">Qté</th>
              <th class="py-2 pr-4">PU</th>
              <th class="py-2 pr-4">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="it in order.items" :key="it.id" class="border-t">
              <td class="py-2 pr-4">{{ it.type }}</td>
              <td class="py-2 pr-4">#{{ it.ref_id }}</td>
              <td class="py-2 pr-4">{{ it.name }}</td>
              <td class="py-2 pr-4">{{ it.quantity }}</td>
              <td class="py-2 pr-4">{{ it.unit }} {{ order.currency }}</td>
              <td class="py-2 pr-4 font-medium">{{ it.total }} {{ order.currency }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Paiements (facultatif, n'affiche que s'il y en a) -->
    <div v-if="order.payments && order.payments.length" class="bg-white rounded-2xl shadow p-5">
      <h2 class="font-semibold text-darknavy mb-3">Paiements</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-gray-600">
              <th class="py-2 pr-4">#</th>
              <th class="py-2 pr-4">Méthode</th>
              <th class="py-2 pr-4">Montant</th>
              <th class="py-2 pr-4">Statut</th>
              <th class="py-2 pr-4">Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in order.payments" :key="p.id" class="border-t">
              <td class="py-2 pr-4">{{ p.id }}</td>
              <td class="py-2 pr-4">{{ p.method }}</td>
              <td class="py-2 pr-4">{{ p.amount }} {{ order.currency }}</td>
              <td class="py-2 pr-4">
                <span class="text-xs px-2 py-1 rounded-full" :class="badgeClass(p.status)">{{ p.status }}</span>
              </td>
              <td class="py-2 pr-4">{{ p.created_at }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
