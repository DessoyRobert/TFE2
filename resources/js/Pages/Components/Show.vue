<script setup>
// Store Pinia du build temporaire
import { useBuildStore } from '@/stores/buildStore'
import { computed } from 'vue'

// Inertia router
import { router } from '@inertiajs/vue3'

// Props attendues depuis Inertia
const props = defineProps({
  component: { type: Object, required: true },
  type: { type: String, required: true }
})

// Store local du build en cours
const buildStore = useBuildStore()

// Vérifie si le composant est déjà dans le build
const dejaAjoute = computed(() =>
  buildStore.build[props.type]?.id === props.component.id
)

// Ajoute le composant au build dans le store Pinia
function ajouterAuBuild() {
  buildStore.addComponent(props.type, props.component)
}

// Retour vers la page de création du build
function allerAuBuild() {
  router.visit('/builds/create')
}
</script>

<template>
  <div class="min-h-screen bg-[#23213a] flex flex-col items-center py-10">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl px-8 py-8 relative">

      <!-- Logo en watermark -->
      <img src="/images/logo-jarvistech.png" alt="JarvisTech Logo"
           class="h-10 absolute left-8 top-8 opacity-70" />

      <div class="flex flex-col md:flex-row gap-8">
        <!-- Image produit -->
        <div class="flex-shrink-0 flex items-center">
          <img :src="component.img_url"
               :alt="component.name"
               class="rounded-xl w-52 h-52 object-contain bg-[#f3f8f7]" />
        </div>

        <!-- Détails -->
        <div class="flex-1 flex flex-col justify-between">
          <div>
            <h1 class="text-3xl font-bold text-[#23213a] mb-2">{{ component.name }}</h1>

            <div class="flex items-center gap-3 mb-2">
              <span class="inline-block bg-[#1ec3a6] text-white text-xs rounded px-3 py-1 font-semibold">
                {{ component.brand }}
              </span>
              <span v-if="component.price" class="text-lg font-semibold text-[#1ec3a6]">
                {{ component.price }} €
              </span>
            </div>

            <p class="text-gray-700 text-base mb-4">
              {{ component.description }}
            </p>

            <!-- Spécifications principales -->
            <ul class="mb-4 grid grid-cols-2 gap-2 text-sm text-[#23213a]">
              <li v-if="component.socket"><span class="font-semibold">Socket :</span> {{ component.socket }}</li>
              <li v-if="component.core_count"><span class="font-semibold">Cœurs :</span> {{ component.core_count }}</li>
              <li v-if="component.thread_count"><span class="font-semibold">Threads :</span> {{ component.thread_count }}</li>
              <li v-if="component.base_clock"><span class="font-semibold">Base Clock :</span> {{ component.base_clock }} GHz</li>
              <li v-if="component.boost_clock"><span class="font-semibold">Boost Clock :</span> {{ component.boost_clock }} GHz</li>
              <li v-if="component.tdp"><span class="font-semibold">TDP :</span> {{ component.tdp }} W</li>
            </ul>
          </div>

          <!-- Bouton ajout au build -->
          <div>
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
