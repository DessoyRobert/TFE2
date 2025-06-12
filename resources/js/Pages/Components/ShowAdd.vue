<script setup>
// Store Pinia du build temporaire
import { useBuildStore } from '@/stores/buildStore'
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

// Props attendues depuis Inertia
const props = defineProps({
  component: { type: Object, required: true },
  // "type" est optionnel, on va le détecter dynamiquement si absent
  type: { type: String, required: false }
})

// Store Pinia du build
const buildStore = useBuildStore()

// On déduit le type (ex: 'cpu', 'gpu', etc.)
const resolvedType = computed(() => {
  // Priorité à la prop, sinon déduction depuis la structure du composant
  if (props.type) return props.type
  if (props.component.type && props.component.type.name)
    return props.component.type.name.toLowerCase()
  if (props.component.component_type && props.component.component_type.name)
    return props.component.component_type.name.toLowerCase()
  return ''
})

// Vérifie si ce composant est déjà dans le build
const dejaAjoute = computed(() => {
  const selected = buildStore.build[resolvedType.value]
  return selected && (
    (selected.id === props.component.id) ||
    (selected.component_id === props.component.id)
  )
})

// Ajoute le composant au build dans le store Pinia
function ajouterAuBuild() {
  if (!resolvedType.value) {
    alert('Type de composant inconnu, impossible d\'ajouter au build.')
    return
  }
  buildStore.addComponent(resolvedType.value, props.component)
}

// Retour vers la page de création du build
function allerAuBuild() {
  router.visit('/builds/create')
}

// Affiche le nom de la marque
function getBrandName(comp) {
  if (!comp) return ''
  if (typeof comp.brand === 'string') return comp.brand
  if (comp.brand && typeof comp.brand === 'object') return comp.brand.name
  return ''
}
</script>

<template>
  <div class="min-h-screen bg-[#23213a] flex flex-col items-center py-10">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg px-6 py-8 md:px-10 md:py-10 flex flex-col items-center relative">
      <!-- Logo watermark discret -->
      <img src="/images/logo-jarvistech.png"
           alt="JarvisTech Logo"
           class="h-7 absolute left-6 top-6 opacity-40 pointer-events-none select-none" />

      <div class="flex flex-col md:flex-row w-full gap-8 md:gap-6 items-center">
        <!-- Image produit -->
        <div class="flex-shrink-0 flex items-center justify-center w-36 h-36 md:w-40 md:h-40 bg-[#f3f8f7] rounded-xl overflow-hidden">
          <img :src="component.img_url"
               :alt="component.name"
               class="object-contain w-full h-full" />
        </div>

        <!-- Détails -->
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

          <!-- Spécifications principales -->
          <ul class="mb-4 grid grid-cols-2 gap-x-4 gap-y-1 text-sm text-[#23213a]">
            <li v-if="component.socket"><span class="font-semibold">Socket :</span> {{ component.socket }}</li>
            <li v-if="component.core_count"><span class="font-semibold">Cœurs :</span> {{ component.core_count }}</li>
            <li v-if="component.thread_count"><span class="font-semibold">Threads :</span> {{ component.thread_count }}</li>
            <li v-if="component.base_clock"><span class="font-semibold">Base Clock :</span> {{ component.base_clock }} GHz</li>
            <li v-if="component.boost_clock"><span class="font-semibold">Boost Clock :</span> {{ component.boost_clock }} GHz</li>
            <li v-if="component.tdp"><span class="font-semibold">TDP :</span> {{ component.tdp }} W</li>
          </ul>

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

    <!-- Lien retour build -->
    <button
      class="mt-8 text-[#1ec3a6] hover:underline font-medium"
      @click="allerAuBuild"
    >
      Voir mon build en cours
    </button>
  </div>
</template>
