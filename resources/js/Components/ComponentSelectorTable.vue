<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import axios from 'axios'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  endpoint: {
    type: String,
    required: true
  }
})

// Emission
const emit = defineEmits(['select'])

const components = ref([])   // Tous les composants de l'API
const search = ref('')

// Tri
const sortBy = ref('name')
const sortDesc = ref(false)

function setSort(column) {
  if (sortBy.value === column) {
    sortDesc.value = !sortDesc.value
  } else {
    sortBy.value = column
    sortDesc.value = false
  }
}

// Chargement
async function fetchComponents() {
  try {
    const response = await axios.get(props.endpoint)
    components.value = response.data
  } catch (error) {
    console.error('Erreur lors du chargement des composants:', error)
    components.value = []
  }
}

// Filtre + tri
const filteredComponents = computed(() => {
  const term = search.value.toLowerCase()
  let list = components.value.filter(c =>
    c.name?.toLowerCase().includes(term) ||
    (c.brand || '').toLowerCase().includes(term) ||
    (c.type || '').toLowerCase().includes(term)
  )
  // Tri :
  list = [...list].sort((a, b) => {
    let valA = a[sortBy.value]
    let valB = b[sortBy.value]
    if (sortBy.value === 'price') {
      valA = parseFloat(valA)
      valB = parseFloat(valB)
      if (isNaN(valA)) valA = 0
      if (isNaN(valB)) valB = 0
    } else {
      valA = (valA ?? '').toString().toLowerCase()
      valB = (valB ?? '').toString().toLowerCase()
    }
    if (valA < valB) return sortDesc.value ? 1 : -1
    if (valA > valB) return sortDesc.value ? -1 : 1
    return 0
  })
  return list
})

onMounted(fetchComponents)
watch(() => props.endpoint, fetchComponents)
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
            Nom
            <span v-if="sortBy === 'name'">{{ sortDesc ? '▼' : '▲' }}</span>
          </th>
          <th @click="setSort('type')" class="px-4 py-2 text-left cursor-pointer select-none">
            Type
            <span v-if="sortBy === 'type'">{{ sortDesc ? '▼' : '▲' }}</span>
          </th>
          <th @click="setSort('brand')" class="px-4 py-2 text-left cursor-pointer select-none">
            Marque
            <span v-if="sortBy === 'brand'">{{ sortDesc ? '▼' : '▲' }}</span>
          </th>
          <th @click="setSort('price')" class="px-4 py-2 text-left cursor-pointer select-none">
            Prix
            <span v-if="sortBy === 'price'">{{ sortDesc ? '▼' : '▲' }}</span>
          </th>
          <th class="px-4 py-2 text-left">Action</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="component in filteredComponents"
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
              :href="`/components/add/${component.id}`"
              class="bg-darkgray hover:bg-gray-700 text-white text-xs px-3 py-1 rounded-xl"
            >
              Détails
            </Link>
          </td>
        </tr>
        <tr v-if="filteredComponents.length === 0">
          <td colspan="5" class="text-center text-darkgray px-4 py-4 italic">
            Aucun composant trouvé.
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
