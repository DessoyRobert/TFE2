<script setup>
import { router, Link } from '@inertiajs/vue3'
import { reactive } from 'vue'
import GoBackButton from '@/Components/GoBackButton.vue'

const props = defineProps({
  builds: { type: Object, required: true }
})

// Map des états de requête pour chaque build
const toggling = reactive({})

function destroy(id) {
  if (confirm('Supprimer ce build ?')) {
    router.delete(route('admin.builds.destroy', id), { preserveScroll: true })
  }
}

function goToPage(url) {
  if (!url) return
  router.visit(url, { preserveScroll: true })
}

function toggleVisibility(build) {
  if (toggling[build.id]) return

  const target = !build.is_public
  const previous = build.is_public

  // Optimistic UI
  build.is_public = target
  toggling[build.id] = true

  router.patch(
    route('admin.builds.toggle-visibility', build.id),
    { is_public: target },
    {
      preserveScroll: true,
      preserveState: true,
      onError: () => {
        build.is_public = previous
        alert('Impossible de mettre à jour la visibilité.')
      },
      onFinish: () => {
        toggling[build.id] = false
      }
    }
  )
}

function formatPrice(value) {
  const n = Number(value ?? 0)
  return n.toLocaleString('fr-BE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>

<template>
  <div class="max-w-6xl mx-auto py-10 space-y-8">
    <GoBackButton class="mb-4" />
    <h1 class="text-2xl font-bold text-darknavy">Gestion des Builds</h1>

    <div v-if="builds?.data?.length > 0">
      <table class="w-full text-sm bg-white rounded-xl shadow border">
        <thead class="bg-lightgray text-darknavy font-semibold">
          <tr>
            <th class="px-4 py-2 text-left">Nom</th>
            <th class="px-4 py-2 text-left">Description</th>
            <th class="px-4 py-2 text-left">Prix</th>
            <th class="px-4 py-2 text-left">Public</th>
            <th class="px-4 py-2 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="build in builds.data"
            :key="build.id"
            class="border-b last:border-0 hover:bg-lightgray/50"
          >
            <td class="px-4 py-2 font-medium">{{ build.name }}</td>
            <td class="px-4 py-2">{{ build.description || '—' }}</td>
            <td class="px-4 py-2">
              {{ formatPrice(build.total_price ?? build.price) }} €
            </td>

            <!-- Switch Public -->
            <td class="px-4 py-2">
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  :aria-pressed="build.is_public"
                  :disabled="toggling[build.id]"
                  @click="toggleVisibility(build)"
                  class="relative inline-flex h-6 w-11 items-center rounded-full transition
                         focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary
                         disabled:opacity-50 disabled:cursor-not-allowed"
                  :class="build.is_public ? 'bg-green-500' : 'bg-gray-300'"
                  title="Basculer visibilité"
                >
                  <span class="sr-only">Basculer visibilité</span>
                  <span
                    class="inline-block h-4 w-4 transform bg-white rounded-full transition"
                    :class="build.is_public ? 'translate-x-6' : 'translate-x-1'"
                  />
                </button>
                <span
                  class="text-xs font-medium"
                  :class="build.is_public ? 'text-green-600' : 'text-gray-500'"
                >
                  {{ build.is_public ? 'Public' : 'Privé' }}
                </span>
              </div>
            </td>

            <td class="px-4 py-2 space-x-2">
              <Link :href="route('admin.builds.edit', build.id)" class="text-blue-600 underline">Éditer</Link>
              <button @click="destroy(build.id)" class="text-red-600 underline">Supprimer</button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="flex gap-2 mt-4 justify-center">
        <button
          v-for="link in builds.links"
          :key="link.label"
          :disabled="!link.url || link.active"
          @click="goToPage(link.url)"
          v-html="link.label"
          class="px-2 py-1 border rounded disabled:opacity-50"
        />
      </div>
    </div>

    <div v-else class="text-center text-gray-600 py-10">
      Aucun build trouvé.
    </div>
  </div>
</template>
