<template>
  <div>
    <table v-if="components.length" class="w-full text-sm">
      <thead class="bg-lightgray text-darknavy">
        <tr>
          <th class="text-left px-4 py-2">Nom</th>
          <th class="text-left px-4 py-2">Prix</th>
          <th class="text-left px-4 py-2">Action</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="component in components"
          :key="component.id"
          class="border-b odd:bg-lightgray even:bg-white"
        >
          <td class="px-4 py-3">{{ component.name }}</td>
          <td class="px-4 py-3">{{ component.price }} €</td>
          <td class="px-4 py-3">
            <button
              class="bg-primary hover:bg-cyan text-white px-3 py-1 rounded-xl text-xs"
              @click="$emit('select', component)"
            >
              Sélectionner
            </button>
          </td>
        </tr>
      </tbody>
    </table>
    <p v-else class="text-darkgray italic">Aucun composant trouvé.</p>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'

const props = defineProps({
  endpoint: String,
})
const emit = defineEmits(['select'])

const components = ref([])

const fetchComponents = async () => {
  try {
    const res = await fetch(props.endpoint)
    const data = await res.json()

    // Si l'API retourne un objet avec "data"
    components.value = Array.isArray(data) ? data : data.data ?? []
  } catch (error) {
    console.error('Erreur lors du chargement des composants :', error)
    components.value = []
  }
}

onMounted(fetchComponents)
watch(() => props.endpoint, fetchComponents)
</script>
