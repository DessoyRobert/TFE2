<script setup>
import { ref, onMounted } from 'vue'
import { useCartStore } from '@/stores/cartStore'

const cart = useCartStore()

const form = ref({
  customer_first_name: '',
  customer_last_name: '',
  customer_email: '',
  customer_phone: '',
  shipping_address_line1: '',
  shipping_address_line2: '',
  shipping_city: '',
  shipping_postal_code: '',
  shipping_country: 'BE',
  payment_method: 'transfer',
  currency: 'EUR'
})

const loading = ref(false)
const result = ref(null)
const errors = ref({})

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

async function submit () {
  loading.value = true
  errors.value = {}
  try {
    const res = await fetch('/checkout', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json' // important pour 422 JSON
      },
      body: JSON.stringify({
        ...form.value,
        items: cart.items
      })
    })

    if (!res.ok) {
      const data = await res.json().catch(() => ({}))
      // 422 validation → data.errors
      errors.value = data.errors ?? { general: ['Erreur serveur'] }
      return
    }

    const data = await res.json()
    result.value = data
    cart.clear()
  } finally {
    loading.value = false
  }
}

</script>


<template>
  <div class="max-w-5xl mx-auto space-y-6">
    <h1 class="text-2xl font-semibold">Checkout</h1>

    <div class="grid md:grid-cols-3 gap-6">
      <!-- Infos client -->
      <div class="md:col-span-2 space-y-3 bg-white p-4 rounded-2xl shadow">
        <div class="grid grid-cols-2 gap-3">
          <input v-model="form.customer_first_name" class="border p-2 rounded" placeholder="Prénom"/>
          <input v-model="form.customer_last_name" class="border p-2 rounded" placeholder="Nom"/>
        </div>
        <input v-model="form.customer_email" class="border p-2 rounded w-full" placeholder="Email"/>
        <input v-model="form.customer_phone" class="border p-2 rounded w-full" placeholder="Téléphone (optionnel)"/>

        <hr class="my-2"/>

        <input v-model="form.shipping_address_line1" class="border p-2 rounded w-full" placeholder="Adresse"/>
        <input v-model="form.shipping_address_line2" class="border p-2 rounded w-full" placeholder="Complément (optionnel)"/>
        <div class="grid grid-cols-3 gap-3">
          <input v-model="form.shipping_city" class="border p-2 rounded" placeholder="Ville"/>
          <input v-model="form.shipping_postal_code" class="border p-2 rounded" placeholder="Code postal"/>
          <input v-model="form.shipping_country" class="border p-2 rounded" placeholder="Pays"/>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <select v-model="form.payment_method" class="border p-2 rounded">
            <option value="transfer">Virement</option>
            <option value="cod">Paiement à la livraison</option>
            <!-- Stripe/PayPal à intégrer plus tard -->
          </select>
          <input v-model="form.currency" class="border p-2 rounded" placeholder="Devise"/>
        </div>

        <div v-if="errors.general" class="text-red-600 text-sm" v-text="errors.general[0]" />
      </div>

      <!-- Récap panier -->
      <aside class="bg-white p-4 rounded-2xl shadow space-y-3 h-fit sticky top-4">
        <h2 class="font-semibold">Récapitulatif</h2>
        <div v-if="!cart.items.length" class="text-sm text-gray-500">Panier vide.</div>
        <ul v-else class="space-y-1 text-sm">
          <li v-for="(it, idx) in cart.items" :key="idx" class="flex justify-between">
            <span>{{ it.type }} #{{ it.id }} × {{ it.qty }}</span>
          </li>
        </ul>
        <button :disabled="loading || !cart.items.length" @click="submit"
                class="w-full py-2 rounded-2xl bg-blue-600 text-white disabled:opacity-50">
          {{ loading ? 'Traitement...' : 'Passer la commande' }}
        </button>

        <div v-if="result" class="text-sm text-green-700">
          Commande #{{ result.order_id }} créée ({{ result.amounts.grand }} {{ result.currency ?? 'EUR' }}).
        </div>
      </aside>
    </div>
  </div>
</template>
