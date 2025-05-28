<template>
  <div class="space-y-4">
    <h2 class="text-xl font-bold text-darknavy">Sélectionner un {{ title }}</h2>

    <input
      v-model="search"
      type="text"
      :placeholder="`Rechercher un ${title}...`"
      class="w-full border px-3 py-1 rounded"
    />

    <ul class="grid md:grid-cols-2 gap-4">
      <li
        v-for="item in filteredItems"
        :key="item.id"
        class="rounded-xl bg-white shadow-md border p-4 flex justify-between items-center"
      >
        <div>
          <h3 class="font-semibold">{{ item.name }}</h3>
          <p v-if="item.description" class="text-sm text-gray-600">{{ item.description }}</p>
        </div>
        <button
          @click="selectItem(item)"
          class="bg-primary hover:bg-cyan text-white px-4 py-2 rounded-xl"
        >
          Ajouter
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  endpoint: { type: String, required: true },
  title: { type: String, default: 'composant' }
})
const emit = defineEmits(['select'])

const search = ref('')
const items = ref([])

const filteredItems = computed(() =>
  items.value.filter(item =>
    item.name.toLowerCase().includes(search.value.toLowerCase())
  )
)

async function fetchItems() {
  try {
    const res = await axios.get(props.endpoint)
    items.value = res.data
  } catch (error) {
    console.error(`Erreur de chargement depuis ${props.endpoint}`, error)
  }
}

function selectItem(item) {
  emit('select', item)
}

onMounted(fetchItems)
</script>
