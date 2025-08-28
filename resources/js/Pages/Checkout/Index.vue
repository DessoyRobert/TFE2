<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCartStore } from '@/stores/cartStore'
import { usePage } from '@inertiajs/vue3'

const cart = useCartStore()
const page = usePage()

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
  payment_method: 'bank_transfer',
  currency: 'EUR',
})

const loading = ref(false)
const result = ref(null)
const errors = ref({})
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

const hasItems = computed(() => cart.items.length > 0)
const normalizedItems = computed(() =>
  cart.items.map(it => ({
    type: it.type, // 'build' | 'component'
    id: Number(it.id),
    qty: Number(it.qty || 1),
  }))
)

onMounted(() => {
  // Pré-remplir depuis l’utilisateur connecté s’il existe
  const u = page?.props?.auth?.user
  if (u) {
    if (!form.value.customer_email && u.email) form.value.customer_email = u.email
    if (!form.value.customer_first_name && u.name) {
      const parts = String(u.name).trim().split(/\s+/)
      form.value.customer_first_name = parts[0] || ''
      form.value.customer_last_name = parts.slice(1).join(' ')
    }
  }
})

async function submit () {
  loading.value = true
  errors.value = {}

  try {
    const payload = {
      ...form.value,
      shipping_country: (form.value.shipping_country || 'BE').toUpperCase(),
      currency: (form.value.currency || 'EUR').toUpperCase(),
      payment_method: form.value.payment_method || 'bank_transfer',
      items: normalizedItems.value,
    }

    const res = await fetch('/checkout', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
        'Accept': 'application/json',
      },
      body: JSON.stringify(payload),
    })

    const data = await res.json().catch(() => ({}))

    if (!res.ok) {
      // 422: { errors: { field: [msg] } }
      errors.value = data.errors ?? { general: ['Erreur serveur'] }
      return
    }

    result.value = data
    cart.clear()
  } finally {
    loading.value = false
  }
}

function copyToClipboard(text) {
  if (!text) return
  navigator.clipboard?.writeText(text)
}
</script>

<template>
  <div class="max-w-5xl mx-auto space-y-6 py-6">
    <h1 class="text-2xl font-semibold">Checkout</h1>

    <!-- État succès (instructions de virement) -->
    <div v-if="result" class="grid md:grid-cols-3 gap-6">
      <div class="md:col-span-2 space-y-4 bg-white p-5 rounded-2xl shadow">
        <h2 class="text-xl font-bold">Commande créée</h2>
        <p class="text-gray-700">
          Merci ! Votre commande <strong>#{{ result.order_id }}</strong> a été créée.
        </p>

        <div v-if="result.bank" class="mt-4 space-y-3">
          <h3 class="font-semibold text-darknavy">Instructions de virement</h3>
          <div class="grid sm:grid-cols-2 gap-3 text-sm">
            <div class="p-3 rounded-lg bg-gray-50 border">
              <div class="text-gray-500">Titulaire</div>
              <div class="font-medium">{{ result.bank.holder }}</div>
            </div>
            <div class="p-3 rounded-lg bg-gray-50 border">
              <div class="text-gray-500">IBAN</div>
              <div class="font-medium">{{ result.bank.iban }}</div>
            </div>
            <div class="p-3 rounded-lg bg-gray-50 border">
              <div class="text-gray-500">BIC</div>
              <div class="font-medium">{{ result.bank.bic }}</div>
            </div>
            <div class="p-3 rounded-lg bg-gray-50 border">
              <div class="text-gray-500">Référence</div>
              <div class="flex items-center gap-2">
                <span class="font-medium">{{ result.bank.reference }}</span>
                <button
                  class="text-xs px-2 py-1 rounded bg-blue-600 text-white"
                  @click="copyToClipboard(result.bank.reference)"
                >
                  Copier
                </button>
              </div>
            </div>
          </div>
          <p v-if="result.bank.payment_deadline" class="text-sm text-gray-700">
            Merci d’effectuer le virement avant le
            <strong>{{ result.bank.payment_deadline }}</strong>.
          </p>
        </div>

        <div class="mt-4">
          <h3 class="font-semibold">Récapitulatif</h3>
          <ul class="text-sm text-gray-700">
            <li>Sous-total : <strong>{{ result.amounts.subtotal }} {{ result.currency ?? 'EUR' }}</strong></li>
            <li>Livraison : <strong>{{ result.amounts.shipping }} {{ result.currency ?? 'EUR' }}</strong></li>
            <li>TVA : <strong>{{ result.amounts.tax }} {{ result.currency ?? 'EUR' }}</strong></li>
            <li v-if="Number(result.amounts.discount)">Réduction : <strong>-{{ result.amounts.discount }} {{ result.currency ?? 'EUR' }}</strong></li>
            <li class="mt-2 text-lg">Total : <strong>{{ result.amounts.grand }} {{ result.currency ?? 'EUR' }}</strong></li>
          </ul>
        </div>
      </div>

      <aside class="bg-white p-4 rounded-2xl shadow space-y-3 h-fit sticky top-4">
        <h2 class="font-semibold">Statut</h2>
        <p class="text-sm text-gray-700">
          Commande #{{ result.order_id }} — <strong>{{ result.status }}</strong><br>
          Paiement : <strong>{{ result.payment_status }}</strong>
        </p>
      </aside>
    </div>

    <!-- Formulaire + Récap (quand pas encore validé) -->
    <div v-else class="grid md:grid-cols-3 gap-6">
      <!-- Infos client -->
      <div class="md:col-span-2 space-y-3 bg-white p-5 rounded-2xl shadow">
        <div class="grid grid-cols-2 gap-3">
          <div>
            <input v-model="form.customer_first_name" class="border p-2 rounded w-full" placeholder="Prénom"/>
            <p v-if="errors.customer_first_name" class="text-sm text-red-600 mt-1">{{ errors.customer_first_name[0] }}</p>
          </div>
          <div>
            <input v-model="form.customer_last_name" class="border p-2 rounded w-full" placeholder="Nom"/>
            <p v-if="errors.customer_last_name" class="text-sm text-red-600 mt-1">{{ errors.customer_last_name[0] }}</p>
          </div>
        </div>

        <div>
          <input v-model="form.customer_email" class="border p-2 rounded w-full" placeholder="Email"/>
          <p v-if="errors.customer_email" class="text-sm text-red-600 mt-1">{{ errors.customer_email[0] }}</p>
        </div>

        <div>
          <input v-model="form.customer_phone" class="border p-2 rounded w-full" placeholder="Téléphone (optionnel)"/>
          <p v-if="errors.customer_phone" class="text-sm text-red-600 mt-1">{{ errors.customer_phone[0] }}</p>
        </div>

        <hr class="my-2"/>

        <div>
          <input v-model="form.shipping_address_line1" class="border p-2 rounded w-full" placeholder="Adresse"/>
          <p v-if="errors.shipping_address_line1" class="text-sm text-red-600 mt-1">{{ errors.shipping_address_line1[0] }}</p>
        </div>

        <div>
          <input v-model="form.shipping_address_line2" class="border p-2 rounded w-full" placeholder="Complément (optionnel)"/>
          <p v-if="errors.shipping_address_line2" class="text-sm text-red-600 mt-1">{{ errors.shipping_address_line2[0] }}</p>
        </div>

        <div class="grid grid-cols-3 gap-3">
          <div>
            <input v-model="form.shipping_city" class="border p-2 rounded w-full" placeholder="Ville"/>
            <p v-if="errors.shipping_city" class="text-sm text-red-600 mt-1">{{ errors.shipping_city[0] }}</p>
          </div>
          <div>
            <input v-model="form.shipping_postal_code" class="border p-2 rounded w-full" placeholder="Code postal"/>
            <p v-if="errors.shipping_postal_code" class="text-sm text-red-600 mt-1">{{ errors.shipping_postal_code[0] }}</p>
          </div>
          <div>
            <input v-model="form.shipping_country" class="border p-2 rounded w-full" placeholder="Pays (ex: BE)"/>
            <p v-if="errors.shipping_country" class="text-sm text-red-600 mt-1">{{ errors.shipping_country[0] }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <select v-model="form.payment_method" class="border p-2 rounded w-full">
              <option value="bank_transfer">Virement</option>
              <option value="cod">Paiement à la livraison</option>
            </select>
            <p v-if="errors.payment_method" class="text-sm text-red-600 mt-1">{{ errors.payment_method[0] }}</p>
          </div>

          <div>
            <input v-model="form.currency" class="border p-2 rounded w-full" placeholder="Devise (ex: EUR)"/>
            <p v-if="errors.currency" class="text-sm text-red-600 mt-1">{{ errors.currency[0] }}</p>
          </div>
        </div>

        <p v-if="errors.general" class="text-red-600 text-sm mt-1">{{ errors.general[0] }}</p>
      </div>

      <!-- Récap panier -->
      <aside class="bg-white p-4 rounded-2xl shadow space-y-4 h-fit sticky top-4">
        <h2 class="font-semibold">Récapitulatif</h2>

        <div v-if="!hasItems" class="text-sm text-gray-500">
          Panier vide.
        </div>

        <ul v-else class="space-y-1 text-sm">
          <li
            v-for="(it, idx) in cart.items"
            :key="idx"
            class="flex justify-between"
          >
            <span class="capitalize">{{ it.type }} #{{ it.id }}</span>
            <span>× {{ it.qty }}</span>
          </li>
        </ul>

        <button
          :disabled="loading || !hasItems"
          @click="submit"
          class="w-full py-2 rounded-2xl bg-blue-600 text-white disabled:opacity-50"
        >
          {{ loading ? 'Traitement...' : 'Passer la commande' }}
        </button>
      </aside>
    </div>
  </div>
</template>
