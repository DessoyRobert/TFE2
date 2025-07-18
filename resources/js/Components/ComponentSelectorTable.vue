<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  endpoint: { type: String, required: true }
})

// Emission
const emit = defineEmits(['select'])

const pagination = ref({
  data: [],
  links: [],
  meta: {}
})
const loading = ref(false)
const search = ref('')
const sortBy = ref('name')
const sortDesc = ref(false)
const perPage = 15

async function fetchComponents(page = 1) {
  loading.value = true
  try {
    const response = await axios.get(props.endpoint, {
      params: {
        page,
        per_page: perPage,
        search: search.value,
        sortBy: sortBy.value,
        sortDesc: sortDesc.value
      }
    })
    pagination.value = response.data
  } catch (error) {
    console.error('Erreur lors du chargement des composants:', error)
    pagination.value = { data: [], links: [], meta: {} }
  } finally {
    loading.value = false
  }
}

function setSort(column) {
  if (sortBy.value === column) {
    sortDesc.value = !sortDesc.value
  } else {
    sortBy.value = column
    sortDesc.value = false
  }
  fetchComponents(1)
}

function goToPage(link) {
  if (!link.url || link.active) return
  const url = new URL(link.url, window.location.origin)
  const page = url.searchParams.get('page') || 1
  fetchComponents(page)
}

watch([search], () => fetchComponents(1))
onMounted(() => fetchComponents(1))
</script>

<template>
  <div class="space-y-4">
    <!-- Barre de recherche -->
    <input
      v-model="search"
      type="text"
      placeholder="Rechercher un composant"
      class="w-full border px-4 py-2 rounded-xl shadow-sm"
    />

    <!-- Tableau filtré/trié -->
    <table class="w-full text-sm border rounded-xl overflow-hidden">
      <thead class="bg-lightgray text-darknavy font-semibold">
        <tr>
          <th @click="setSort('name')" class="px-4 py-2 text-left cursor-pointer select-none">
            Nom <span v-if="sortBy === 'name'">{{ sortDesc ? '▼' : '▲' }}</span>
          </th>
          <th @click="setSort('type')" class="px-4 py-2 text-left cursor-pointer select-none">
            Type <span v-if="sortBy === 'type'">{{ sortDesc ? '▼' : '▲' }}</span>
          </th>
          <th @click="setSort('brand')" class="px-4 py-2 text-left cursor-pointer select-none">
            Marque <span v-if="sortBy === 'brand'">{{ sortDesc ? '▼' : '▲' }}</span>
          </th>
          <th @click="setSort('price')" class="px-4 py-2 text-left cursor-pointer select-none">
            Prix <span v-if="sortBy === 'price'">{{ sortDesc ? '▼' : '▲' }}</span>
          </th>
          <th class="px-4 py-2 text-left">Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td colspan="5" class="text-center text-darkgray px-4 py-4 italic">
            Chargement...
          </td>
        </tr>
        <tr
          v-for="component in pagination.data"
          :key="component.id"
          class="border-b last:border-0 hover:bg-lightgray/50 transition"
        >
          <td class="px-4 py-2">{{ component.name }}</td>
          <td class="px-4 py-2">{{ component.type || '—' }}</td>
          <td class="px-4 py-2">{{ component.brand || '—' }}</td>
          <td class="px-4 py-2">{{ component.price !== undefined ? `${component.price} €` : '—' }}</td>
          <td class="px-4 py-2 flex gap-2">
            <button
              @click="$emit('select', component)"
              class="bg-primary hover:bg-cyan text-white text-xs px-3 py-1 rounded-xl"
            >
              Sélectionner
            </button>
            <Link
              :href="`/components/${component.id}/details`"
              class="bg-darkgray bg-gray-700 text-white text-xs px-3 py-1 rounded-xl"
            >
              Détails
            </Link>
          </td>
        </tr>
        <tr v-if="!loading && pagination.data.length === 0">
          <td colspan="5" class="text-center text-darkgray px-4 py-4 italic">
            Aucun composant trouvé.
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="flex gap-2 mt-4 justify-center">
      <button
        v-for="link in pagination.links"
        :key="link.label"
        :disabled="!link.url || link.active"
        @click="goToPage(link)"
        v-html="link.label"
        class="px-2 py-1 border rounded"
      />
    </div>
  </div>
</template>
