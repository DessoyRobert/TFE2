<script setup>
import { defineProps } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  builds: {
    type: Array,
    required: true
  },
  isAdmin: {
    type: Boolean,
    default: false
  }
})


function goToShow(id) {
  router.visit(`/builds/${id}`)
}

function goToEdit(id) {
  router.visit(`/builds/${id}/edit`)
}

function destroyBuild(id) {
  if (confirm('Supprimer ce build ?')) {
    router.delete(`/builds/${id}`)
  }
}
</script>

<template>
  <div class="max-w-5xl mx-auto py-10 space-y-8">
    <h1 class="text-2xl font-bold text-darknavy mb-6">Liste des builds</h1>

    <div v-if="builds.length === 0" class="text-darkgray italic p-8 text-center">
      Aucun build trouvé.
    </div>

    <table v-else class="w-full text-sm border rounded-xl bg-white shadow">
      <thead class="bg-lightgray text-darknavy font-semibold">
        <tr>
          <th class="px-4 py-2 text-left">Nom</th>
          <th class="px-4 py-2 text-left">Prix</th>
          <th class="px-4 py-2 text-left">Composants</th>
          <th class="px-4 py-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="build in builds"
          :key="build.id"
          class="border-b last:border-0 hover:bg-lightgray/50 transition"
        >
          <td class="px-4 py-3 font-medium">
            {{ build.name }}
          </td>
          <td class="px-4 py-3">
            {{ (parseFloat(build.price) || 0).toFixed(2) }} €
          </td>
          <td class="px-4 py-3">
            <ul class="list-disc ml-5 space-y-1">
              <li
                v-for="component in build.components"
                :key="component.id"
                class="text-darknavy"
              >
                {{ component.name }} <span class="text-xs text-darkgray">({{ component.brand?.name ?? '—' }})</span>
              </li>
            </ul>
          </td>
          <td class="px-4 py-3 space-x-2">
            <button
              class="bg-primary hover:bg-cyan text-white px-3 py-1 rounded-xl text-xs"
              @click="goToShow(build.id)"
            >Voir</button>
            <button
            v-if="isAdmin"
              class="bg-darknavy hover:bg-violetdark text-white px-3 py-1 rounded-xl text-xs"
              @click="goToEdit(build.id)"
            >Éditer</button>
            <button
            v-if="isAdmin"
              class="bg-red-500 hover:bg-red-700 text-white px-3 py-1 rounded-xl text-xs"
              @click="destroyBuild(build.id)"
            >Supprimer</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
