<script setup>
// Pinia store
import { useBuildStore } from '@/stores/buildStore'
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'

// Props depuis Inertia
const props = defineProps({
  component: { type: Object, required: true },
  details:   { type: Object, required: true },
  type:      { type: String, required: false },
})

const buildStore  = useBuildStore()
const justAdded   = ref(false)
const errorMsg    = ref('')

/** Libellé brut (ce que renvoie l’API / la page) */
const rawType = computed(() => {
  if (props.type) return props.type
  const c = props.component
  return c?.type?.name ?? c?.type ?? c?.component_type?.name ?? c?.component_type ?? ''
})

/** Clé normale utilisée par le store (cpu, gpu, case_model, …) */
const storeKey = computed(() => buildStore.normalizeType?.(rawType.value) ?? '')

/** Sélection actuelle dans le slot */
const selectedInSlot = computed(() => (storeKey.value ? buildStore.build?.[storeKey.value] : null))

/** Déjà ajouté (même id dans le slot) */
const dejaAjoute = computed(() => {
  const cur = selectedInSlot.value
  const id  = props.component?.id
  if (!cur || !id) return false
  return (cur.id === id) || (cur.component_id === id)
})

/** Slot occupé par un autre composant → proposer “Remplacer” */
const peutRemplacer = computed(() => {
  const cur = selectedInSlot.value
  const id  = props.component?.id
  return !!(cur && id && !dejaAjoute.value)
})

/** Brand & prix formatés */
const brandName = computed(() => {
  const b = props.component?.brand
  if (!b) return ''
  return typeof b === 'string' ? b : (b.name ?? '')
})
const eur = new Intl.NumberFormat('fr-BE', { style: 'currency', currency: 'EUR' })
const priceText = computed(() => {
  const n = Number(props.component?.price ?? NaN)
  return Number.isFinite(n) ? eur.format(n) : ''
})

/** Image avec transformation Cloudinary (fallback image statique) */
function cld(url, { w = 480, h = 480 } = {}) {
  if (!url) return '/images/default.png'
  try {
    const isCld = url.includes('/upload/')
    if (!isCld) return url
    return url.replace('/upload/', `/upload/f_auto,q_auto,w_${w},h_${h},c_fit/`)
  } catch { return url }
}
const imageUrl = computed(() => {
  const url = props.component?.images?.[0]?.url || props.component?.img_url
  return cld(url || '/images/default.png', { w: 480, h: 480 })
})

/** Détails affichables (garde l’ordre naturel, formate proprement) */
function formatKey(key) { return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }
function formatValue(value) {
  if (Array.isArray(value)) return value.join(', ')
  if (typeof value === 'boolean') return value ? 'Oui' : 'Non'
  if (value === null || value === undefined || value === '') return '—'
  return String(value)
}
const displayDetails = computed(() => {
  const d = props.details || {}
  // On limite à 14 lignes max pour éviter un pavé (ajuste au besoin)
  return Object.entries(d).slice(0, 14)
})

/** Actions */
function ajouterOuRemplacer() {
  errorMsg.value = ''
  try {
    if (!storeKey.value) {
      errorMsg.value = "Type de composant inconnu — impossible d'ajouter."
      return
    }
    // addFromComponent gère l’ajout/replace côté store
    buildStore.addFromComponent?.(props.component)
    buildStore.validateBuild?.()

    justAdded.value = true
    setTimeout(() => { justAdded.value = false }, 1500)
  } catch (e) {
    console.error(e)
    errorMsg.value = "Une erreur est survenue lors de l'ajout au build."
  }
}
function allerAuBuild() { router.visit('/builds/create') }
function retourListe()   { router.visit('/components') }
</script>

<template>
  <div class="min-h-screen flex flex-col items-center py-10">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl px-6 py-8 md:px-10 md:py-10 relative">
      <!-- Logo -->
      <img
        src="https://res.cloudinary.com/djllwl8c0/image/upload/f_auto,q_auto,w_192,h_48,c_fit/Logo-JarvisTech-PNG-normalsansfond_pgxlrj.png"
        alt="Logo JarvisTech"
        class="h-12 w-auto bg-darknavy/90 p-1.5 rounded-full absolute top-4 left-4 shadow-md"
      />

      <!-- En-tête composant -->
      <div class="flex flex-col md:flex-row w-full gap-8 items-center">
        <div class="flex-shrink-0 flex items-center justify-center w-40 h-40 md:w-48 md:h-48 bg-[#f3f8f7] rounded-xl overflow-hidden">
          <img :src="imageUrl" :alt="component.name" class="object-contain w-full h-full" />
        </div>

        <div class="flex-1 w-full">
          <h1 class="text-2xl md:text-3xl font-extrabold text-[#23213a] mb-2 break-words">
            {{ component.name }}
          </h1>

          <div class="flex flex-wrap items-center gap-2 mb-3">
            <span
              v-if="brandName"
              class="inline-block bg-[#1ec3a6] text-white text-xs font-bold rounded px-2 py-0.5"
            >{{ brandName }}</span>

            <span
              v-if="rawType"
              class="inline-block bg-slate-100 text-slate-700 text-[11px] font-semibold rounded px-2 py-0.5"
            >{{ rawType }}</span>

            <span v-if="priceText" class="text-lg font-semibold text-[#1ec3a6]">
              {{ priceText }}
            </span>
          </div>

          <p v-if="component.description" class="text-gray-700 text-sm mb-3 leading-relaxed">
            {{ component.description }}
          </p>

          <!-- Détails -->
          <ul
            v-if="displayDetails.length"
            class="mb-5 grid grid-cols-2 gap-x-4 gap-y-1 text-sm text-[#23213a]"
          >
            <li v-for="[k, v] in displayDetails" :key="k">
              <span class="font-semibold">{{ formatKey(k) }}:</span>
              {{ formatValue(v) }}
            </li>
          </ul>

          <!-- Message d’erreur simple -->
          <p v-if="errorMsg" class="mb-3 text-sm text-red-600">
            {{ errorMsg }}
          </p>

          <!-- CTA principal -->
          <button
            :disabled="dejaAjoute"
            @click="ajouterOuRemplacer"
            class="w-full bg-[#1ec3a6] hover:bg-[#23b59b] text-white font-bold py-3 rounded-xl shadow-lg transition
                   disabled:opacity-60 disabled:cursor-not-allowed"
            aria-live="polite"
          >
            <template v-if="justAdded">Ajouté ✓</template>
            <template v-else-if="dejaAjoute">Déjà ajouté au build</template>
            <template v-else-if="peutRemplacer">Remplacer dans mon build</template>
            <template v-else>Ajouter au build</template>
          </button>

          <!-- Lien rapide vers le builder -->
          <div class="mt-3 flex items-center justify-between text-sm">
            <button @click="allerAuBuild" class="text-[#1ec3a6] hover:underline font-medium">
              Voir mon build en cours →
            </button>
            <button @click="retourListe" class="text-slate-500 hover:underline">
              ← Revenir à la liste
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
