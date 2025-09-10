<script setup>
import { router, Link } from '@inertiajs/vue3'
import GoBackButton from '@/Components/GoBackButton.vue'

const props = defineProps({
  users: {
    type: Object,
    required: true,
  }
})

function destroy(id) {
  if (confirm('Supprimer cet utilisateur ?')) {
    router.delete(route('admin.users.destroy', id))
  }
}
function goToPage(url) {
  if (!url) return
  router.visit(url)
}
</script>

<template>
  <div class="max-w-4xl mx-auto py-10">
    <GoBackButton class="mb-4" />
    <h1 class="text-2xl font-bold text-darknavy mb-6">Utilisateurs</h1>

    <table class="w-full bg-white rounded-xl shadow border text-sm">
      <thead class="bg-lightgray text-darknavy font-semibold">
        <tr>
          <th class="px-4 py-2 text-left">Nom</th>
          <th class="px-4 py-2 text-left">Email</th>
          <th class="px-4 py-2 text-left">Admin</th>
          <th class="px-4 py-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users.data" :key="user.id" class="border-b last:border-0">
          <td class="px-4 py-2">{{ user.name }}</td>
          <td class="px-4 py-2">{{ user.email }}</td>
          <td class="px-4 py-2">
            <span v-if="user.is_admin" class="text-green-600 font-bold">Oui</span>
            <span v-else class="text-gray-500">Non</span>
          </td>
          <td class="px-4 py-2 space-x-2">
            <!-- Optionnel : édition -->
            <Link
              :href="route('admin.users.edit', user.id)"
              class="text-blue-600 underline hover:text-blue-800"
            >Éditer</Link>
            <button
              class="text-red-600 underline hover:text-red-800"
              @click="destroy(user.id)"
              type="button"
            >Supprimer</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div v-if="users.links" class="flex gap-2 mt-4 justify-center">
      <button
        v-for="link in users.links"
        :key="link.label"
        :disabled="!link.url || link.active"
        @click="goToPage(link.url)"
        v-html="link.label"
        class="px-2 py-1 border rounded"
      />
    </div>
  </div>
</template>
