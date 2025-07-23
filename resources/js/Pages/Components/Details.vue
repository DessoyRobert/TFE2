<script setup>
// Pinia store
import { useBuildStore } from '@/stores/buildStore'
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import GoBackButton from '@/Components/GoBackButton.vue'

// Props depuis Inertia
const props = defineProps({
  component: { type: Object, required: true },
  details: { type: Object, required: true },
  type: { type: String, required: false },
})

const buildStore = useBuildStore()

const resolvedType = computed(() => {
  if (props.type) return props.type
  if (props.component.type && props.component.type.name)
    return props.component.type.name.toLowerCase()
  if (props.component.component_type && props.component.component_type.name)
    return props.component.component_type.name.toLowerCase()
  return ''
})

const dejaAjoute = computed(() => {
  const selected = buildStore.build[resolvedType.value]
  return selected && (
    (selected.id === props.component.id) ||
    (selected.component_id === props.component.id)
  )
})

function ajouterAuBuild() {
  if (!resolvedType.value) {
    alert('Type de composant inconnu, impossible d\'ajouter au build.')
    return
  }
  buildStore.addComponent(resolvedType.value, props.component)
}

function allerAuBuild() {
  router.visit('/builds/create')
}

function getBrandName(comp) {
  if (!comp) return ''
  if (typeof comp.brand === 'string') return comp.brand
  if (comp.brand && typeof comp.brand === 'object') return comp.brand.name
  return ''
}

function formatKey(key) {
  return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

function formatValue(value) {
  if (Array.isArray(value)) return value.join(', ')
  if (typeof value === 'boolean') return value ? 'Oui' : 'Non'
  return value ?? '—'
}
</script>

<template>
  <div class="min-h-screen flex flex-col items-center py-10">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg px-6 py-8 md:px-10 md:py-10 flex flex-col items-center relative">
            <img
  src="https://res.cloudinary.com/djllwl8c0/image/upload/v1753292540/Logo-JarvisTech-PNG-normalsansfond_pgxlrj.png"
  alt="Logo JarvisTech"
  class="h-14 w-auto bg-darknavy p-1 rounded-full absolute top-4 left-4 shadow-md"
/>



      <div class="flex flex-col md:flex-row w-full gap-8 md:gap-6 items-center">
        <div class="flex-shrink-0 flex items-center justify-center w-36 h-36 md:w-40 md:h-40 bg-[#f3f8f7] rounded-xl overflow-hidden">
          <img
            :src="component.images?.[0]?.url ?? '/images/default.png'"
            :alt="component.name"
            class="object-contain w-full h-full"
          />
        </div>

        <div class="flex-1 flex flex-col justify-center w-full">
          <h1 class="text-2xl md:text-3xl font-extrabold text-[#23213a] mb-1 break-words">{{ component.name }}</h1>

          <div class="flex items-center gap-2 mb-3">
            <span v-if="getBrandName(component)" class="inline-block bg-[#1ec3a6] text-white text-xs font-bold rounded px-2 py-0.5">
              {{ getBrandName(component) }}
            </span>
            <span v-if="component.price" class="text-lg font-semibold text-[#1ec3a6]">
              {{ component.price }} €
            </span>
          </div>

          <p v-if="component.description" class="text-gray-700 text-sm mb-2 leading-relaxed">
            {{ component.description }}
          </p>

          <!-- Détails dynamiques -->
          <ul v-if="details && Object.keys(details).length"
              class="mb-4 grid grid-cols-2 gap-x-4 gap-y-1 text-sm text-[#23213a]">
            <li v-for="(value, key) in details" :key="key">
              <span class="font-semibold">{{ formatKey(key) }}:</span>
              {{ formatValue(value) }}
            </li>
          </ul>

          <!-- Bouton ajouter -->
          <button
            :disabled="dejaAjoute"
            @click="ajouterAuBuild"
            class="w-full bg-[#1ec3a6] hover:bg-[#23b59b] text-white font-bold py-3 rounded-xl shadow-lg transition disabled:opacity-60 disabled:cursor-not-allowed"
          >
            {{ dejaAjoute ? 'Déjà ajouté au build' : 'Ajouter au build' }}
          </button>
        </div>
      </div>
    </div>

    <div class="w-full max-w-lg mb-4 px-6 md:px-0">
      <button
        @click="router.visit('/components')"
        class="flex items-center text-sm text-[#1ec3a6] font-medium hover:underline"
      >
        ← Aller à la liste des composants
      </button>
    </div>

    <!-- Lien vers build -->
    <button
      class="mt-8 text-[#1ec3a6] hover:underline font-medium"
      @click="allerAuBuild"
    >
      Voir mon build en cours
    </button>
  </div>
</template>
