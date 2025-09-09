<script setup>
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { useBuildStore } from '@/stores/buildStore'

const props = defineProps({
  components: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const build = useBuildStore()

/* ------------------ Filtres locaux (init depuis props) ------------------ */
const filters = ref({
  search: props.filters?.search ?? '',
  sortBy: props.filters?.sortBy ?? '',
  sortDesc: props.filters?.sortDesc ?? false,
})

/* ------------------ Navigation avec Inertia ------------------ */
function applyFilters({ resetPage = true } = {}) {
  router.get(route?.('components.index') ?? '/components', {
    ...filters.value,
    ...(resetPage ? { page: 1 } : {}),
  }, {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  })
}

/* Debounce recherche */
let debounceId = null
watch(() => filters.value.search, () => {
  clearTimeout(debounceId)
  debounceId = setTimeout(() => applyFilters({ resetPage: true }), 300)
})

/* Tri / sens */
watch(() => [filters.value.sortBy, filters.value.sortDesc], () => {
  applyFilters({ resetPage: true })
})

/* ------------------ UI helpers ------------------ */
const eur = new Intl.NumberFormat('fr-BE', { style: 'currency', currency: 'EUR' })

const added = ref({})
function addToBuild(component) {
  if (!component?.id) return
  build.addFromComponent(component)
  added.value = { ...added.value, [component.id]: true }
  setTimeout(() => {
    const copy = { ...added.value }
    delete copy[component.id]
    added.value = copy
  }, 1500)
}

function toggleSortDir() {
  filters.value.sortDesc = !filters.value.sortDesc
}
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-10 text-slate-900">
    <h1 class="text-3xl font-semibold mb-6">Liste des composants</h1>

    <div class="flex flex-wrap items-center gap-4 mb-6">
      <label class="sr-only" for="search">Rechercher</label>
      <input
        id="search"
        v-model="filters.search"
        type="text"
        placeholder="Rechercher…"
        class="w-64 px-4 py-2 border border-slate-300 rounded-lg focus:ring focus:ring-cyan-300 focus:outline-none"
        autocomplete="off"
      />

      <label class="sr-only" for="sortBy">Trier par</label>
      <select
        id="sortBy"
        v-model="filters.sortBy"
        class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring focus:ring-cyan-300"
      >
        <option value="">Trier par</option>
        <option value="name">Nom</option>
        <option value="price">Prix</option>
        <option value="component_type_id">Type</option>
      </select>

      <button
        type="button"
        @click="toggleSortDir"
        class="px-3 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg text-sm"
        :aria-pressed="filters.sortDesc ? 'true' : 'false'"
      >
        {{ filters.sortDesc ? '↓ Descendant' : '↑ Ascendant' }}
      </button>
    </div>

    <div class="overflow-x-auto bg-white shadow rounded-xl border border-slate-200">
      <table class="w-full text-left table-auto">
        <thead class="bg-slate-100 text-slate-600 text-sm">
          <tr>
            <th class="px-4 py-3">Nom</th>
            <th class="px-4 py-3">Marque</th>
            <th class="px-4 py-3">Type</th>
            <th class="px-4 py-3 text-right">Prix</th>
            <th class="px-4 py-3 text-center">Actions</th>
          </tr>
        </thead>

        <tbody class="text-slate-800">
          <tr v-if="!components?.data?.length">
            <td colspan="5" class="px-4 py-6 text-center text-slate-500">
              Aucun résultat. Essaie d’élargir ta recherche ou de modifier le tri.
            </td>
          </tr>

          <tr
            v-for="component in components.data"
            :key="component.id"
            class="border-t border-slate-200 hover:bg-slate-50 transition"
          >
            <td class="px-4 py-3">
              {{ component.name }}
            </td>

            <td class="px-4 py-3">
              {{ component.brand?.name ?? component.brand ?? '—' }}
            </td>

            <td class="px-4 py-3 capitalize">
              {{ component.type?.name ?? component.type ?? '—' }}
            </td>

            <td class="px-4 py-3 text-right">
              {{ eur.format(Number(component.price ?? 0)) }}
            </td>

            <td class="px-4 py-3">
              <div class="flex items-center gap-2 justify-center">
                <Link
                  :href="`/components/${component.id}/details`"
                  class="inline-block text-xs px-3 py-1 rounded-full bg-[#1ec3a6] text-white font-semibold shadow hover:bg-[#1aa893] transition"
                >
                  Détails
                </Link>

                <button
                  type="button"
                  @click="addToBuild(component)"
                  :disabled="!!added[component.id]"
                  class="inline-flex items-center text-xs px-3 py-1 rounded-full font-semibold shadow transition
                         text-white disabled:opacity-70 disabled:cursor-not-allowed
                         bg-blue-600 hover:bg-blue-700"
                >
                  <span v-if="!added[component.id]">Ajouter au Build</span>
                  <span v-else class="inline-flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3.25-3.25a1 1 0 111.414-1.414l2.543 2.543 6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Ajouté
                  </span>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center">
      <nav class="flex flex-wrap gap-1">
        <template v-for="(link, i) in components.links" :key="link.url ?? i">
          <component
            :is="link.url ? Link : 'span'"
            :href="link.url || undefined"
            v-html="link.label"
            class="px-3 py-2 text-sm rounded-md"
            :class="{
              'bg-cyan-500 text-white font-bold': link.active,
              'text-slate-600 hover:bg-slate-100 cursor-pointer': !link.active && link.url,
              'text-slate-400 cursor-not-allowed': !link.url
            }"
            v-bind="link.url ? { preserveScroll: true } : {}"
          />
        </template>
      </nav>
    </div>
  </div>
</template>
