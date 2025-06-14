<script setup>
import { router } from '@inertiajs/vue3'
import GoBackButton from '@/Components/GoBackButton.vue'

// On attend maintenant un objet paginé !
const props = defineProps({ categories: Object })

function destroy(id) {
  if (confirm('Confirmer la suppression ?')) {
    router.delete(route('admin.categories.destroy', id))
  }
}

// Pagination
function goToPage(url) {
  if (!url) return
  router.visit(url)
}
</script>

<template>
  <div class="max-w-3xl mx-auto py-10 space-y-8">
    <GoBackButton />
    <h1 class="text-2xl font-bold text-darknavy">Catégories</h1>
    <a :href="route('admin.categories.create')"
       class="bg-primary hover:bg-cyan text-white px-4 py-2 rounded-xl shadow">Ajouter une catégorie</a>
    <table class="w-full mt-4 bg-white rounded-xl shadow border">
      <thead>
        <tr>
          <th class="px-4 py-2 text-left">Nom</th>
          <th class="px-4 py-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="category in categories.data" :key="category.id" class="border-b last:border-0 hover:bg-lightgray/50">
          <td class="px-4 py-2">{{ category.name }}</td>
          <td class="px-4 py-2 space-x-2">
            <a :href="route('admin.categories.edit', category.id)"
               class="text-blue-600 underline hover:text-blue-800">Éditer</a>
            <button @click="destroy(category.id)"
               class="text-red-600 underline hover:text-red-800">Supprimer</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="flex gap-2 mt-4 justify-center">
      <button
        v-for="link in categories.links"
        :key="link.label"
        :disabled="!link.url || link.active"
        @click="goToPage(link.url)"
        v-html="link.label"
        class="px-2 py-1 border rounded"
      />
    </div>
  </div>
</template>
