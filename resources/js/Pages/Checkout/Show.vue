<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const order = page.props.order

const fmt = (v) => {
  try { return new Intl.NumberFormat('fr-BE',{style:'currency',currency: order.currency||'EUR'}).format(Number(v||0)) }
  catch { return `${Number(v||0).toFixed(2)} ${order.currency||'EUR'}` }
}
</script>

<template>
  <div class="max-w-3xl mx-auto p-6 space-y-6">
    <h1 class="text-2xl font-bold">Commande #{{ order.id }}</h1>

    <div class="grid sm:grid-cols-2 gap-4">
      <div class="p-4 rounded-xl border">
        <h2 class="font-semibold mb-2">Montants</h2>
        <div class="flex justify-between"><span>Sous-total</span><span>{{ fmt(order.amounts.subtotal) }}</span></div>
        <div class="flex justify-between"><span>Livraison</span><span>{{ fmt(order.amounts.shipping) }}</span></div>
        <div class="flex justify-between"><span>TVA</span><span>{{ fmt(order.amounts.tax) }}</span></div>
        <div class="flex justify-between"><span>Réduction</span><span>- {{ fmt(order.amounts.discount) }}</span></div>
        <div class="border-t mt-2 pt-2 flex justify-between font-semibold">
          <span>Total</span><span>{{ fmt(order.amounts.grand) }}</span>
        </div>
      </div>

      <div v-if="order.bank?.reference" class="p-4 rounded-xl border">
        <h2 class="font-semibold mb-2">Paiement par virement</h2>
        <p>Référence: <strong>{{ order.bank.reference }}</strong></p>
        <p>Date limite: <strong>{{ order.bank.deadline }}</strong></p>
      </div>
    </div>

    <div class="p-4 rounded-xl border">
      <h2 class="font-semibold mb-2">Articles</h2>
      <ul class="space-y-1">
        <li v-for="it in order.items" :key="`${it.type}-${it.id}`" class="flex justify-between">
          <span>{{ it.type }} — {{ it.name }}</span>
          <span>{{ it.quantity }} × {{ fmt(it.unit_price) }} = <strong>{{ fmt(it.line_total) }}</strong></span>
        </li>
      </ul>
    </div>

    <div class="flex gap-3">
      <Link href="/orders" class="px-4 py-2 rounded-xl bg-darknavy text-white">Voir mes commandes</Link>
      <Link href="/builds" class="px-4 py-2 rounded-xl border">Continuer mes achats</Link>
    </div>
  </div>
</template>
