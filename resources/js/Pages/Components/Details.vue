<!-- resources/js/Pages/Components/Details.vue -->
<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AddToBuildButton from '@/Components/ui/AddToBuildButton.vue'
import { safeImg } from '@/utils/imageHelpers'

const props = defineProps({
  component: { type: Object, required: true },
  details:   { type: Object, required: true },
  type:      { type: String, required: false },
})

/* ------------ Méta / affichage ------------ */
const rawType = computed(() => {
  if (props.type) return props.type
  const c = props.component
  return c?.type?.name ?? c?.type ?? c?.component_type?.name ?? c?.component_type ?? ''
})

const brandName = computed(() => {
  const b = props.component?.brand
  return typeof b === 'string' ? b : (b?.name ?? '')
})

const eur = new Intl.NumberFormat('fr-BE', { style: 'currency', currency: 'EUR' })
const priceText = computed(() => {
  const n = Number(props.component?.price ?? NaN)
  return Number.isFinite(n) ? eur.format(n) : ''
})

/* ------------ Image principale (fallback inclus) ------------ */
const imageUrl = computed(() =>
  safeImg(props.component?.images?.[0]?.url || props.component?.img_url, 480)
)

/* ------------ Détails lisibles ------------ */
function formatKey(key) { return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }
function formatValue(value) {
  if (Array.isArray(value)) return value.join(', ')
  if (typeof value === 'boolean') return value ? 'Oui' : 'Non'
  if (value === null || value === undefined || value === '') return '—'
  return String(value)
}

const displayDetails = computed(() => {
  const d = props.details || {}
  // enlève quelques champs techniques si présents
  const cleaned = Object.fromEntries(
    Object.entries(d).filter(([k]) => !['id','component_id','created_at','updated_at'].includes(k))
  )
  return Object.entries(cleaned).slice(0, 14)
})

/* ------------ Liens rapides ------------ */
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
          <ul v-if="displayDetails.length" class="mb-5 grid grid-cols-2 gap-x-4 gap-y-1 text-sm text-[#23213a]">
            <li v-for="[k, v] in displayDetails" :key="k">
              <span class="font-semibold">{{ formatKey(k) }}:</span>
              {{ formatValue(v) }}
            </li>
          </ul>

          <!-- CTA: bouton réutilisable -->
          <AddToBuildButton :component="component" size="lg" :show-price="true" />

          <!-- Liens -->
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
