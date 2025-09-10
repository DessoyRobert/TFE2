<script setup>
import { ref } from 'vue'
import { router, Link, useForm } from '@inertiajs/vue3'
import GoBackButton from '@/Components/GoBackButton.vue'

const props = defineProps({
  componentTypes: {
    type: Object, // paginated object si tu fais une pagination sinon Array
    required: true
  }
})

// Formulaire ajout inline
const addForm = useForm({ name: '' })

function submitAdd() {
  addForm.post(route('admin.component-types.store'), {
    onSuccess: () => addForm.reset()
  })
}

function destroy(id) {
  if (confirm('Supprimer ce type de composant ?')) {
    router.delete(route('admin.component-types.destroy', id))
  }
}

function goToPage(url) {
  if (!url) return
  router.visit(url)
}
</script>

<template>
  <div class="max-w-3xl mx-auto py-10">
    <GoBackButton class="mb-4" />
    <h1 class="text-2xl font-bold text-darknavy mb-6">Types de composants</h1>

    <!-- Ajout rapide -->
    <form @submit.prevent="submitAdd" class="flex gap-2 mb-6">
      <input
        v-model="addForm.name"
        class="border rounded-xl px-3 py-2 flex-1"
        placeholder="Nom du type de composant"
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
        <tr v-for="type in componentTypes.data" :key="type.id" class="border-b last:border-0">
          <td class="px-4 py-2 font-medium">{{ type.name }}</td>
          <td class="px-4 py-2 space-x-2">
            <Link
              :href="route('admin.component-types.edit', type.id)"
              class="text-blue-600 underline hover:text-blue-800"
            >
              Éditer
            </Link>
            <button
              class="text-red-600 underline hover:text-red-800"
              @click="destroy(type.id)"
              type="button"
            >
              Supprimer
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination si tu l'utilises -->
    <div v-if="componentTypes.links" class="flex gap-2 mt-4 justify-center">
      <button
        v-for="link in componentTypes.links"
        :key="link.label"
        :disabled="!link.url || link.active"
        @click="goToPage(link.url)"
        v-html="link.label"
        class="px-2 py-1 border rounded"
      />
    </div>
  </div>
</template>
