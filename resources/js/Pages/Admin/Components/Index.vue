<script setup>
// Import Inertia
import { router } from '@inertiajs/vue3'
import GoBackButton from '@/Components/GoBackButton.vue'
// Props reçues depuis Inertia
defineProps({
  components: Array
})

// Suppression d’un composant avec confirmation
function destroy(id) {
  if (confirm('Confirmer la suppression de ce composant ?')) {
    router.delete(route('admin.components.destroy', id))
  }
}
</script>

<template>
  <div class="max-w-6xl mx-auto py-10 space-y-8"><GoBackButton class="mb-4" />
    <h1 class="text-2xl font-bold text-darknavy">Liste des composants</h1>

    <!-- Lien pour ajouter un composant -->
    <a
      :href="route('admin.components.create')"
      class="inline-block bg-primary hover:bg-cyan text-white px-4 py-2 rounded-xl shadow transition"
    >
      Ajouter un composant
    </a>

    <!-- Tableau des composants -->
    <table class="w-full mt-4 text-sm bg-white rounded-xl shadow border">
      <thead class="bg-lightgray text-darknavy font-semibold">
        <tr>
          <th class="px-4 py-2 text-left">Nom</th>
          <th class="px-4 py-2 text-left">Marque</th>
          <th class="px-4 py-2 text-left">Type</th>
          <th class="px-4 py-2 text-left">Prix</th>
          <th class="px-4 py-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="component in components"
          :key="component.id"
          class="border-b last:border-0 hover:bg-lightgray/50 transition"
        >
          <td class="px-4 py-2 font-medium">{{ component.name }}</td>
          <td class="px-4 py-2">{{ component.brand?.name ?? '—' }}</td>
          <td class="px-4 py-2">{{ component.type?.name ?? '—' }}</td>
          <td class="px-4 py-2">{{ component.price }} €</td>
          <td class="px-4 py-2 space-x-2">
            <!-- Lien vers édition -->
            <a
              :href="route('admin.components.edit', component.id)"
              class="text-blue-600 underline hover:text-blue-800"
            >
              Éditer
            </a>

            <!-- Formulaire de suppression -->
            <form
              :action="route('admin.components.destroy', component.id)"
              method="POST"
              style="display: inline"
              @submit.prevent="destroy(component.id)"
            >
              <input type="hidden" name="_method" value="DELETE" />
              <button
                type="submit"
                class="text-red-600 underline hover:text-red-800"
              >
                Supprimer
              </button>
            </form>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
