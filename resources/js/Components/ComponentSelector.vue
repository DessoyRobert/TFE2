<script setup>
import { ref, computed, onMounted } from 'vue'
import { useBuildStore } from '@/Stores/build'

const props = defineProps({
  endpoint: { type: String, required: true },
  title: { type: String, default: 'composant' },
  typeKey: { type: String, required: true } // 'cpu', 'gpu', etc.
})
const emit = defineEmits(['select'])

const buildStore = useBuildStore()
const search = ref('')
const items = ref([])

const filteredItems = computed(() =>
  items.value.filter(item =>
    item.name.toLowerCase().includes(search.value.toLowerCase())
  )
)

async function fetchItems() {
  try {
    const res = await fetch(props.endpoint)
    const data = await res.json()
    items.value = Array.isArray(data) ? data : data.data ?? []
  } catch (error) {
    console.error(`Erreur de chargement depuis ${props.endpoint}`, error)
  }
}

function selectItem(item) {
  emit('select', item)
}

function removeItem() {
  buildStore.build[props.typeKey] = null
}

onMounted(fetchItems)
</script>

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
        <div class="flex gap-2">
          <button
            @click="selectItem(item)"
            class="bg-primary hover:bg-cyan text-white px-4 py-2 rounded-xl"
          >
            Ajouter
          </button>
          <button
            v-if="buildStore.build[props.typeKey]?.id === item.id"
            @click="removeItem"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl"
          >
            Retirer
          </button>
        </div>
      </li>
    </ul>
  </div>
</template>
