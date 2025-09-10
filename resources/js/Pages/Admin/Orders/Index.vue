<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { computed, reactive } from 'vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  orders: { type: Object, required: true }, // Laravel paginator
  filters: { type: Object, default: () => ({ status: null }) },
})

/* ------------------- Helpers UI ------------------- */
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

function formatAmount(value, currency) {
  const n = Number(value ?? 0)
  try { return new Intl.NumberFormat('fr-BE', { style: 'currency', currency: currency || 'EUR' }).format(n) }
  catch { return `${n.toFixed(2)} ${currency || 'EUR'}` }
}

/* ------------------- Filtres & navigation ------------------- */
const ui = reactive({
  status: props.filters?.status || '',
})

const statusFilters = [
  { key: '', label: 'Tous' },
  { key: 'pending', label: 'En attente' },
  { key: 'paid', label: 'Payées' },
  { key: 'preparing', label: 'Préparation' },
  { key: 'shipped', label: 'Expédiées' },
  { key: 'delivered', label: 'Livrées' },
  { key: 'canceled', label: 'Annulées' },
  { key: 'refunded', label: 'Remboursées' },
]

function applyFilters() {
  router.get(route('admin.orders.index'), { status: ui.status || undefined }, {
    preserveScroll: true,
    preserveState: true,
  })
}

function goTo(url) {
  if (!url) return
  // Convertit l’URL paginée en route Inertia en conservant le filtre
  const u = new URL(url, window.location.origin)
  const page = u.searchParams.get('page') || '1'
  router.get(route('admin.orders.index'), { page, status: ui.status || undefined }, {
    preserveScroll: true,
    preserveState: true,
  })
}

/* ------------------- Actions ------------------- */
function updateStatus(id, status) {
  if (!status) return
  router.patch(route('admin.orders.updateStatus', id), { status }, {
    preserveScroll: true,
  })
}

function rowClick(id) {
  router.visit(route('admin.orders.show', id))
}

/* ------------------- Pagination helpers ------------------- */
const hasLinks = computed(() => Array.isArray(props.orders?.links))
</script>

<template>
  <div class="max-w-6xl mx-auto p-4 space-y-6">
    <div class="flex items-center justify-between gap-3">
      <h1 class="text-2xl font-semibold">Commandes</h1>

      <!-- Filtres rapides -->
      <div class="flex items-center gap-2">
        <select v-model="ui.status" @change="applyFilters" class="border rounded-xl px-3 py-2">
          <option v-for="f in statusFilters" :key="f.key" :value="f.key">{{ f.label }}</option>
        </select>
        <button
          class="px-3 py-2 rounded-xl border hover:bg-gray-50"
          @click="() => { ui.status=''; applyFilters() }"
          v-if="ui.status"
        >
          Réinitialiser
        </button>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="border-b bg-gray-50">
          <tr class="text-left text-gray-600">
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

        <tbody v-if="props.orders?.data?.length">
          <tr
            v-for="o in props.orders.data"
            :key="o.id"
            class="border-b hover:bg-gray-50 cursor-pointer"
            @click="rowClick(o.id)"
          >
            <td class="p-3 font-medium">#{{ o.id }}</td>
            <td class="p-3 whitespace-nowrap">{{ o.created_at }}</td>
            <td class="p-3">{{ o.customer }}</td>
            <td class="p-3">{{ o.email }}</td>
            <td class="p-3 font-semibold">{{ formatAmount(o.grand_total, o.currency) }}</td>
            <td class="p-3">
              <span class="text-xs px-2 py-1 rounded-full" :class="badgeClass(o.status)">
                {{ o.status }}
              </span>
            </td>
            <td class="p-3">
              <span class="text-xs px-2 py-1 rounded-full" :class="badgeClass(o.payment_status)">
                {{ o.payment_status }}
              </span>
            </td>
            <td class="p-3" @click.stop>
              <label class="sr-only" :for="`status-${o.id}`">Changer statut</label>
              <select
                :id="`status-${o.id}`"
                class="border px-2 py-1 rounded"
                @change="e => updateStatus(o.id, e.target.value)"
              >
                <option disabled selected>Changer…</option>
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

        <tbody v-else>
          <tr>
            <td class="p-6 text-center text-gray-600" colspan="8">
              Aucune commande trouvée
              <template v-if="ui.status"> pour le filtre “{{ ui.status }}”</template>.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between">
      <div class="text-sm text-gray-500">
        Page {{ props.orders?.current_page }} / {{ props.orders?.last_page }}
      </div>

      <!-- Liens Laravel (links[]) -->
      <div v-if="hasLinks" class="flex items-center gap-2">
        <button
          v-for="link in props.orders.links"
          :key="link.label"
          class="px-3 py-1 rounded-xl border disabled:opacity-50"
          :class="link.active ? 'bg-darknavy text-white border-darknavy' : 'hover:bg-gray-50'"
          :disabled="!link.url"
          v-html="link.label"
          @click="goTo(link.url)"
        />
      </div>

      <!-- Fallback précédent/suivant -->
      <div v-else class="flex gap-2">
        <button
          :disabled="!props.orders?.prev_page_url"
          class="px-3 py-1 rounded-xl border disabled:opacity-50 hover:bg-gray-50"
          @click="goTo(props.orders.prev_page_url)"
        >
          Préc.
        </button>
        <button
          :disabled="!props.orders?.next_page_url"
          class="px-3 py-1 rounded-xl border disabled:opacity-50 hover:bg-gray-50"
          @click="goTo(props.orders.next_page_url)"
        >
          Suiv.
        </button>
      </div>
    </div>
  </div>
</template>
