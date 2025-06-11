<script setup>
import { router } from '@inertiajs/vue3'
defineProps({ builds: Array })
import GoBackButton from '@/Components/GoBackButton.vue'
function destroy(id) {
  if (confirm('Supprimer ce build ?')) {
    router.delete(route('admin.builds.destroy', id))
  }
}
</script>

<template>
  <div class="max-w-6xl mx-auto py-10 space-y-8"><GoBackButton class="mb-4" />
    <h1 class="text-2xl font-bold text-darknavy">Gestion des Builds</h1>
    <table class="w-full text-sm bg-white rounded-xl shadow border">
      <thead class="bg-lightgray text-darknavy font-semibold">
        <tr>
          <th class="px-4 py-2 text-left">Nom</th>
          <th class="px-4 py-2 text-left">Description</th>
          <th class="px-4 py-2 text-left">Prix</th>
          <th class="px-4 py-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="build in builds" :key="build.id" class="border-b last:border-0 hover:bg-lightgray/50">
          <td class="px-4 py-2 font-medium">{{ build.name }}</td>
          <td class="px-4 py-2">{{ build.description }}</td>
          <td class="px-4 py-2">{{ build.price }} €</td>
          <td class="px-4 py-2 space-x-2">
            <Link :href="route('admin.builds.edit', build.id)" class="text-blue-600 underline">Éditer</Link>
            <button @click="destroy(build.id)" class="text-red-600 underline">Supprimer</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
