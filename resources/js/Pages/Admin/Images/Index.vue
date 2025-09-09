<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  images: { type: Object, required: true },   // paginator: { data, links, meta... }
  filters: { type: Object, default: () => ({ q: '' }) },
})

const q = ref(props.filters?.q ?? '')

function search() {
  router.get(route('admin.images.index'), { q: q.value }, { preserveState: true, replace: true })
}

function clearSearch() {
  q.value = ''
  search()
}

function destroy(id) {
  if (!confirm('Supprimer cette image ?')) return
  router.delete(route('admin.images.destroy', id), {
    preserveScroll: true,
  })
}

function makePrimary(id) {
  router.post(route('admin.images.primary', id), {}, { preserveScroll: true })
}

const hasData = computed(() => Array.isArray(props.images?.data) && props.images.data.length > 0)

function ownerLabel(row) {
  return row.owner_label || (row.has_owner ? `${row.type} #${row.ref}` : '—')
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-start justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold">Images</h1>
        <p class="text-gray-600">Gère toutes les images associées aux builds et composants.</p>
      </div>
      <div class="flex items-center gap-2">
        <!-- Lien vers ton formulaire d’upload générique -->
        <Link
          :href="route('images.upload')"
          class="px-4 py-2 rounded-xl bg-darknavy text-white hover:bg-primary"
        >
          Ajouter / Uploader
        </Link>
      </div>
    </div>

    <!-- Barre de recherche -->
    <div class="bg-white rounded-2xl shadow p-4">
      <form @submit.prevent="search" class="flex items-center gap-2">
        <input
          v-model="q"
          type="text"
          class="w-full border rounded-xl px-4 py-2"
          placeholder="Rechercher: url, type (Build/Component), ID lié…"
        />
        <button type="submit" class="px-4 py-2 rounded-xl border hover:bg-gray-50">Rechercher</button>
        <button type="button" class="px-4 py-2 rounded-xl border hover:bg-gray-50" @click="clearSearch">Effacer</button>
      </form>
    </div>

    <!-- Tableau -->
    <div class="bg-white rounded-2xl shadow overflow-hidden">
      <div v-if="!hasData" class="p-6 text-gray-500">Aucune image trouvée.</div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-gray-600 border-b">
              <th class="py-3 px-4">Preview</th>
              <th class="py-3 px-4">ID</th>
              <th class="py-3 px-4">Type</th>
              <th class="py-3 px-4">Lié à</th>
              <th class="py-3 px-4">URL</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in images.data" :key="row.id" class="border-b last:border-0">
              <td class="py-3 px-4">
                <img :src="row.url" alt="" class="w-16 h-16 object-cover rounded-lg border" />
              </td>
              <td class="py-3 px-4 font-mono">#{{ row.id }}</td>
              <td class="py-3 px-4">{{ row.type || '—' }}</td>
              <td class="py-3 px-4">
                <span :class="row.has_owner ? 'text-gray-900' : 'text-gray-400'">{{ ownerLabel(row) }}</span>
              </td>
              <td class="py-3 px-4">
                <a :href="row.url" target="_blank" class="text-blue-600 hover:underline break-all line-clamp-2">
                  {{ row.url }}
                </a>
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center gap-2 justify-end">
                  <!-- Rendre principale (si pertinent) -->
                  <button
                    v-if="row.has_owner && (row.type === 'Build' || row.type === 'Component')"
                    type="button"
                    class="px-3 py-1.5 rounded-lg border hover:bg-gray-50"
                    @click="makePrimary(row.id)"
                    title="Définir comme image principale"
                  >
                    Principale
                  </button>

                  <!-- Supprimer -->
                  <button
                    type="button"
                    class="px-3 py-1.5 rounded-lg border border-red-300 text-red-600 hover:bg-red-50"
                    @click="destroy(row.id)"
                    title="Supprimer l'image"
                  >
                    Supprimer
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination (rend directement les links Laravel/Inertia) -->
      <div v-if="images.links?.length" class="p-3 border-t bg-gray-50">
        <nav class="flex flex-wrap gap-2">
          <Link
            v-for="(l,i) in images.links"
            :key="i"
            :href="l.url || ''"
            :class="[
              'px-3 py-1.5 rounded-lg border',
              l.active ? 'bg-darknavy text-white border-darknavy' : 'hover:bg-white',
              !l.url ? 'opacity-50 pointer-events-none' : ''
            ]"
            v-html="l.label"
            preserve-state
            replace
          />
        </nav>
      </div>
    </div>
  </div>
</template>
