<script setup>
import { ref } from 'vue'
import { router, Link, useForm } from '@inertiajs/vue3'
import GoBackButton from '@/Components/GoBackButton.vue'
defineProps({
  brands: {
    type: Array,
    required: true
  }
})

// Ajout d’une marque (formulaire minimal inline)
const addForm = useForm({ name: '' })

function submitAdd() {
  addForm.post(route('admin.brands.store'), {
    onSuccess: () => addForm.reset()
  })
}

// Suppression
function destroy(id) {
  if (confirm('Supprimer cette marque ?')) {
    router.delete(route('admin.brands.destroy', id))
  }
}
</script>

<template>
  <div class="max-w-3xl mx-auto py-10"><GoBackButton class="mb-4" />
    <h1 class="text-2xl font-bold text-darknavy mb-6">Gestion des marques</h1>

    <!-- Ajout rapide -->
    <form @submit.prevent="submitAdd" class="flex gap-2 mb-6">
      <input
        v-model="addForm.name"
        class="border rounded-xl px-3 py-2 flex-1"
        placeholder="Nom de la marque"
        required
      />
      <button
        type="submit"
        class="bg-primary hover:bg-cyan text-white px-4 py-2 rounded-xl shadow font-semibold"
      >
        Ajouter
      </button>
    </form>

    <table class="w-full bg-white rounded-xl shadow border text-sm">
      <thead class="bg-lightgray text-darknavy font-semibold">
        <tr>
          <th class="px-4 py-2 text-left">Nom</th>
          <th class="px-4 py-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="brand in brands" :key="brand.id" class="border-b last:border-0">
          <td class="px-4 py-2 font-medium">{{ brand.name }}</td>
          <td class="px-4 py-2 space-x-2">
            <!-- Édition (ex: modal ou page dédiée, ici lien) -->
            <Link
              :href="route('admin.brands.edit', brand.id)"
              class="text-blue-600 underline hover:text-blue-800"
            >
              Éditer
            </Link>
            <button
              class="text-red-600 underline hover:text-red-800"
              @click="destroy(brand.id)"
              type="button"
            >
              Supprimer
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
