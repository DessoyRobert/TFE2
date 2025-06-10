<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import axios from 'axios'
import { Link } from '@inertiajs/vue3'

// Props : endpoint à interroger (ex: /api/cpus)
const props = defineProps({
  endpoint: {
    type: String,
    required: true
  }
})

// Émission de l'événement de sélection d'un composant
const emit = defineEmits(['select'])

// Liste des composants chargés depuis l'API
const components = ref([])

// Recherche utilisateur (filtrage client)
const search = ref('')

// Chargement initial des données
async function fetchComponents() {
  try {
    const response = await axios.get(props.endpoint)
    components.value = response.data
  } catch (error) {
    console.error('Erreur lors du chargement des composants:', error)
    components.value = []
  }
}

// Computed local : filtre sur le nom du composant
const filteredComponents = computed(() => {
  const term = search.value.toLowerCase()
  return components.value.filter(c =>
    c.name?.toLowerCase().includes(term) ||
    c.brand?.name?.toLowerCase().includes(term)
  )
})

// Rechargement si l'endpoint change
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

    <!-- Tableau des composants filtrés -->
    <table class="w-full text-sm border rounded-xl overflow-hidden">
      <thead class="bg-lightgray text-darknavy font-semibold">
        <tr>
          <th class="px-4 py-2 text-left">Nom</th>
          <th class="px-4 py-2 text-left">Marque</th>
          <th class="px-4 py-2 text-left">Prix</th>
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
          <td class="px-4 py-2">{{ component.brand?.name ?? '—' }}</td>
          <td class="px-4 py-2">{{ component.price }} €</td>
          <td class="px-4 py-2 flex gap-2">
            <button
              @click="$emit('select', component)"
              class="bg-primary hover:bg-cyan text-white text-xs px-3 py-1 rounded-xl"
            >
              Sélectionner
            </button>
            <Link
              :href="`/components/${component.id}`"
              class="bg-darkgray hover:bg-gray-700 text-white text-xs px-3 py-1 rounded-xl"
            >
              Détails
            </Link>
          </td>
        </tr>

        <tr v-if="filteredComponents.length === 0">
          <td colspan="4" class="text-center text-darkgray px-4 py-4 italic">
            Aucun composant trouvé.
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
