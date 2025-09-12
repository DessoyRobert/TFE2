<script setup>
import { router, Link } from '@inertiajs/vue3'

const props = defineProps({
  builds: { type: Array, default: () => [] },
  orders: { type: Array, default: () => [] },
})

function viewBuild(id) {
  router.visit(`/builds/${id}`)
}

function formatEUR(value, currency = 'EUR') {
  const n = Number(value ?? 0)
  try { return new Intl.NumberFormat('fr-BE', { style:'currency', currency }).format(n) }
  catch { return `${n.toFixed(2)} ${currency}` }
}
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-8 space-y-8">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-darknavy">Mon dashboard</h1>

      <!-- Actions rapides -->
      <div class="flex flex-wrap gap-2">
        <Link href="/builds/create" class="px-4 py-2 rounded-xl bg-darknavy text-white hover:bg-primary">
          Créer un build
        </Link>
        <Link href="/components" class="px-4 py-2 rounded-xl border hover:bg-gray-50">
          Parcourir les composants
        </Link>
        <Link href="/account/orders" class="px-4 py-2 rounded-xl border hover:bg-gray-50">
          Mes commandes
        </Link>
      </div>
    </div>


    <!-- Derniers builds -->
    <div class="space-y-3">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-darknavy">Derniers builds</h2>
        <Link href="/builds" class="text-sm text-blue-700 hover:underline">Voir tous</Link>
      </div>

      <div v-if="!builds.length" class="text-gray-500 bg-white rounded-2xl p-5 shadow">
        Aucun build encore. Lance-toi !
      </div>

      <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="b in builds"
          :key="b.id"
          class="border rounded-xl p-4 bg-white shadow-sm flex flex-col justify-between"
        >
          <div>
            <h3 class="text-base font-semibold text-darknavy">
              {{ b.name || 'Build personnalisé' }}
            </h3>
            <p v-if="b.description" class="text-sm text-gray-500 mb-2">{{ b.description }}</p>
            <p class="text-sm text-gray-600 font-medium">
              Prix : <strong>{{ formatEUR(b.total_price ?? b.price ?? 0) }}</strong>
            </p>
          </div>
          <div class="flex gap-2 mt-4">
            <button
              @click="viewBuild(b.id)"
              class="bg-primary text-white px-4 py-1 rounded-xl text-sm hover:bg-cyan"
            >
              Voir
            </button>
            <Link
              :href="`/checkout?build=${b.id}`"
              class="px-4 py-1 rounded-xl border text-sm hover:bg-gray-50"
            >
              Commander
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Dernières commandes -->
    <div class="space-y-3">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-darknavy">Dernières commandes</h2>
        <Link href="/account/orders" class="text-sm text-blue-700 hover:underline">Voir toutes</Link>
      </div>

      <div v-if="!orders.length" class="text-gray-500 bg-white rounded-2xl p-5 shadow">
        Aucune commande pour le moment.
      </div>

      <div v-else class="bg-white rounded-2xl shadow overflow-x-auto">
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
          <tbody>
            <tr v-for="o in orders" :key="o.id" class="border-b">
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
        </table>
      </div>
    </div>

  </div>
</template>
