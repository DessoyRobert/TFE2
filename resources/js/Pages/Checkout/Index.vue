<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCartStore } from '@/stores/cartStore'
import { usePage, router } from '@inertiajs/vue3'

const cart = useCartStore()
const page = usePage()

/* ------------------- Helpers ------------------- */
function createIdempotencyKey () {
  return (crypto?.randomUUID?.() || `${Date.now()}-${Math.random()}`)
}
function getQueryParam(name) {
  try { return new URLSearchParams(window.location.search).get(name) } catch { return null }
}
function copyToClipboard(text) { if (text) navigator.clipboard?.writeText(text) }

/* ------------------- Form (facultatif) ------------------- */
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

/* ------------------- Cart bindings ------------------- */
const hasItems = computed(() => cart.items.length > 0)
const itemsCount = computed(() => cart.items.reduce((acc, it) => acc + (it.qty ?? 1), 0))
const buildItemInCart = computed(() => cart.items.find(it => it.type === 'build') || null)
const componentIdsInCart = computed(() =>
  cart.items
    .filter(it => it.type === 'component')
    .map(it => Number(it.id))
    .filter(n => Number.isFinite(n))
)

/* Actions panier */
function removeItem (item) { cart.remove({ type: item.type, id: item.id }) }
function clearCart () { if (hasItems.value && confirm('Vider le panier ?')) cart.clear() }

/* ------------------- Paramètre build depuis l’URL -------------------
   - Flux “Sauvegarder & Commander” → /checkout?build=123
   - Ici, on AJOUTE ce build au panier (au lieu de commander auto). */
const buildIdFromQuery = computed(() => {
  const raw = getQueryParam('build')
  const n = Number(raw)
  return Number.isFinite(n) && n > 0 ? n : null
})

/* ------------------- Mapping API → UI ------------------- */
function mapApiToResult(data) {
  return {
    order_id: data?.order_id ?? null,
    status: data?.status ?? 'pending',
    payment_status: data?.payment_status ?? 'unpaid',
    currency: data?.currency ?? 'EUR',
    bank: data?.bank ?? null,
    amounts: data?.amounts ?? {
      subtotal: undefined, shipping: undefined, tax: undefined, discount: undefined, grand: undefined,
    },
    redirect_url: data?.redirect_url ?? null,
  }
}

/* ------------------- API ------------------- */
async function placeOrder({ buildId, componentIds = [] }) {
  loading.value = true
  errors.value = {}
  try {
    // Prefill depuis l'utilisateur connecté
    const u = page?.props?.auth?.user
    if (u) {
      if (!form.value.customer_email && u.email) form.value.customer_email = String(u.email)
      if (!form.value.customer_first_name && !form.value.customer_last_name && u.name) {
        const parts = String(u.name).trim().split(/\s+/)
        form.value.customer_first_name = parts[0] || 'Client'
        form.value.customer_last_name  = parts.slice(1).join(' ') || 'PCBuilder'
      }
    }

    const payload = {
      build_id: Number(buildId),
      // IMPORTANT : on n’envoie des component_ids QUE pour le flux “panier”
      ...(componentIds.length ? { component_ids: componentIds } : {}),
      ...form.value,
      shipping_country: (form.value.shipping_country || 'BE').toUpperCase(),
      currency: (form.value.currency || 'EUR').toUpperCase(),
      payment_method: form.value.payment_method || 'bank_transfer',
    }

    const res = await fetch('/checkout', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
        'Accept': 'application/json',
        'Idempotency-Key': createIdempotencyKey(),
      },
      body: JSON.stringify(payload),
      credentials: 'same-origin',
    })

    const data = await res.json().catch(() => ({}))

    if (res.status === 401) {
      errors.value = { general: ['Connecte-toi pour passer commande.'] }
      return
    }

    if (!res.ok) {
      if (data?.errors) errors.value = data.errors
      else if (data?.message) errors.value = { general: [data.message] }
      else errors.value = { general: ['Erreur serveur inattendue.'] }
      return
    }

    // Succès
    result.value = mapApiToResult(data)

    // Redirection si fournie
    if (result.value.redirect_url) {
      window.location.replace(result.value.redirect_url)
      return
    }
    if (result.value.order_id) {
      router.visit(`/checkout/${result.value.order_id}`)
      return
    }
  } finally {
    loading.value = false
  }
}

/* ------------------- Lifecycle ------------------- */
onMounted(() => {
  // Pré-remplir depuis l’utilisateur connecté (doublon safe avec placeOrder)
  const u = page?.props?.auth?.user
  if (u) {
    if (!form.value.customer_email && u.email) form.value.customer_email = String(u.email)
    if (!form.value.customer_first_name && !form.value.customer_last_name && u.name) {
      const parts = String(u.name).trim().split(/\s+/)
      form.value.customer_first_name = parts[0] || 'Client'
      form.value.customer_last_name  = parts.slice(1).join(' ') || 'PCBuilder'
    }
  }

  // Si ?build=ID est présent, AJOUTE le build au panier (pas de commande auto)
  if (buildIdFromQuery.value) {
    const already = cart.items.find(it =>
      it.type === 'build' && Number(it.id) === Number(buildIdFromQuery.value)
    )
    if (!already) {
      cart.add({ type: 'build', id: Number(buildIdFromQuery.value), qty: 1 })
    }
  }
})

/* ------------------- Action bouton -------------------
   - Si un build est présent dans l’URL → on le retrouve normalement dans le panier via onMounted().
   - On commande le build du panier et (optionnel) les components du panier. */
async function submit () {
  errors.value = {}
  const build = buildItemInCart.value
  if (!build) {
    errors.value = { general: ['Ajoute un build au panier ou ouvre le checkout depuis “Sauvegarder & Commander”.'] }
    return
  }
  await placeOrder({ buildId: build.id, componentIds: componentIdsInCart.value })
}
</script>

<template>
  <div class="max-w-5xl mx-auto space-y-6 py-6">
    <h1 class="text-2xl font-semibold">Checkout</h1>

    <!-- État succès -->
    <div v-if="result" class="grid md:grid-cols-3 gap-6">
      <div class="md:col-span-2 space-y-4 bg-white p-5 rounded-2xl shadow">
        <h2 class="text-xl font-bold">Commande créée</h2>
        <p class="text-gray-700">
          Merci ! Votre commande
          <strong v-if="result.order_id">#{{ result.order_id }}</strong>
          a été créée.
        </p>

        <!-- Bloc virement (optionnel) -->
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

        <!-- Récap montants (optionnel) -->
        <div v-if="result.amounts && (result.amounts.subtotal != null || result.amounts.grand != null)" class="mt-4">
          <h3 class="font-semibold">Récapitulatif</h3>
          <ul class="text-sm text-gray-700">
            <li v-if="result.amounts.subtotal != null">
              Sous-total :
              <strong>{{ result.amounts.subtotal }} {{ result.currency ?? 'EUR' }}</strong>
            </li>
            <li v-if="result.amounts.shipping != null">
              Livraison :
              <strong>{{ result.amounts.shipping }} {{ result.currency ?? 'EUR' }}</strong>
            </li>
            <li v-if="result.amounts.tax != null">
              TVA :
              <strong>{{ result.amounts.tax }} {{ result.currency ?? 'EUR' }}</strong>
            </li>
            <li v-if="result.amounts.discount != null && Number(result.amounts.discount)">
              Réduction :
              <strong>-{{ result.amounts.discount }} {{ result.currency ?? 'EUR' }}</strong>
            </li>
            <li v-if="result.amounts.grand != null" class="mt-2 text-lg">
              Total :
              <strong>{{ result.amounts.grand }} {{ result.currency ?? 'EUR' }}</strong>
            </li>
          </ul>
        </div>
      </div>

      <aside class="bg-white p-4 rounded-2xl shadow space-y-3 h-fit sticky top-4">
        <h2 class="font-semibold">Statut</h2>
        <p class="text-sm text-gray-700">
          Commande
          <template v-if="result.order_id">#{{ result.order_id }} — </template>
          <strong>{{ result.status }}</strong><br>
          Paiement : <strong>{{ result.payment_status }}</strong>
        </p>
      </aside>
    </div>

    <!-- Form + Récap (avant validation) -->
    <div v-else class="grid md:grid-cols-3 gap-6">
      <!-- Infos client (facultatif) -->
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
              <option value="card">Carte</option>
              <option value="cash">Cash</option>
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

      <!-- Récap panier + actions -->
      <aside class="bg-white p-4 rounded-2xl shadow space-y-4 h-fit sticky top-4">
        <div class="flex items-center justify-between">
          <h2 class="font-semibold">Récapitulatif</h2>
          <span class="text-xs text-gray-500" v-if="hasItems">{{ itemsCount }} article(s)</span>
        </div>

        <div v-if="!hasItems" class="text-sm text-gray-500">
          Panier vide. Si tu es arrivé depuis “Sauvegarder & Commander”, le build a été ajouté au panier automatiquement.
        </div>

        <ul v-else class="space-y-2 text-sm">
          <li
            v-for="(it, idx) in cart.items"
            :key="`${it.type}:${it.id}:${idx}`"
            class="flex items-center justify-between gap-2"
          >
            <div class="min-w-0">
              <div class="font-medium truncate capitalize">{{ it.type }} #{{ it.id }}</div>
              <div class="text-gray-500">× {{ it.qty }}</div>
            </div>
            <button
              type="button"
              class="px-2 py-1 rounded border text-gray-700 hover:bg-gray-50"
              @click="removeItem(it)"
              :aria-label="`Retirer ${it.type} #${it.id}`"
            >
              Retirer
            </button>
          </li>
        </ul>

        <div class="flex items-center justify-between gap-2" v-if="hasItems">
          <button
            type="button"
            class="px-3 py-2 rounded-2xl border hover:bg-gray-50"
            @click="clearCart"
          >
            Vider le panier
          </button>

          <button
            :disabled="loading"
            @click="submit"
            class="py-2 px-4 rounded-2xl bg-blue-600 text-white disabled:opacity-50"
          >
            {{ loading ? 'Traitement...' : 'Passer la commande' }}
          </button>
        </div>

        <div v-else>
          <button
            :disabled="loading"
            @click="submit"
            class="w-full py-2 rounded-2xl bg-blue-600 text-white disabled:opacity-50"
          >
            {{ loading ? 'Traitement...' : 'Passer la commande' }}
          </button>
        </div>
      </aside>
    </div>
  </div>
</template>
