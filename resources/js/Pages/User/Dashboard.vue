<script setup>
import { router } from '@inertiajs/vue3'

/**
 * On reçoit déjà `computed_price` depuis l'API grâce à $appends côté PHP.
 * Pas besoin de recalculer en front.
 */
const props = defineProps({
  builds: {
    type: Array,
    default: () => []
  }
})

function viewBuild(id) {
  router.visit(`/builds/${id}`)
}

/** Formateur devise côté client (fr-BE, EUR) */
function formatEUR(value) {
  const n = Number(value ?? 0)
  return n.toLocaleString('fr-BE', { style: 'currency', currency: 'EUR' })
}
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-10 space-y-8">
    <h1 class="text-2xl font-bold text-darknavy">Mon dashboard</h1>

    <div v-if="!builds.length" class="text-gray-500">
      Vous n'avez encore créé aucun build.
    </div>

    <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="build in builds"
        :key="build.id"
        class="border rounded-xl p-4 bg-white shadow-sm flex flex-col justify-between"
      >
        <div>
          <h2 class="text-lg font-semibold text-darknavy">
            {{ build.name || 'Build personnalisé' }}
          </h2>
          <p v-if="build.description" class="text-sm text-gray-500 mb-2">
            {{ build.description }}
          </p>

          <!-- Prix fiable : computed_price fourni par le modèle Laravel -->
          <p class="text-sm text-gray-600 font-medium">
            Prix :
            <strong>{{ formatEUR(build.computed_price ?? 0) }}</strong>
          </p>
        </div>

        <div class="flex gap-2 mt-4">
          <button
            @click="viewBuild(build.id)"
            class="bg-primary text-white px-4 py-1 rounded-xl text-sm hover:bg-cyan"
          >
            Voir
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
