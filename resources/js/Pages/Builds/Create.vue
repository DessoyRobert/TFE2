<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'

import BuildFormFields from '@/Components/BuildFormFields.vue'
import ComponentSelectorTable from '@/Components/ComponentSelectorTable.vue'

const build = ref({
  name: '',
  description: '',
  imgUrl: '',
  cpu: null,
  gpu: null,
  ram: null,
  motherboard: null,
  storage: null,
  psu: null,
  cooler: null,
  case_model: null,
})

const componentTypes = [
  { key: 'cpu', label: 'Processeur', endpoint: '/api/cpus' },
  { key: 'gpu', label: 'Carte graphique', endpoint: '/api/gpus' },
  { key: 'ram', label: 'Mémoire RAM', endpoint: '/api/rams' },
  { key: 'motherboard', label: 'Carte mère', endpoint: '/api/motherboards' },
  { key: 'storage', label: 'Stockage', endpoint: '/api/storages' },
  { key: 'psu', label: 'Alimentation', endpoint: '/api/psus' },
  { key: 'cooler', label: 'Refroidissement', endpoint: '/api/coolers' },
  { key: 'case_model', label: 'Boîtier', endpoint: '/api/case-models' },
]

const autoPrice = computed(() => {
  return componentTypes.reduce((total, t) => {
    const comp = build.value[t.key]
    return total + (comp?.price ? Number(comp.price) : 0)
  }, 0)
})

const selectorKey = ref(null)

function handleSelect(key) {
  selectorKey.value = selectorKey.value === key ? null : key
}

async function submitBuild() {
  // Prépare uniquement le payload nécessaire pour l'API
  const payload = {
    name: build.value.name,
    description: build.value.description,
    imgUrl: build.value.imgUrl,
    price: autoPrice.value,
    components: [],
  }

  // Ajoute chaque component_id sélectionné
  for (const type of componentTypes) {
    const comp = build.value[type.key]
    // Check sur component_id (le vrai ID global)
    if (comp && comp.component_id) {
      payload.components.push({ component_id: comp.component_id })
    }
  }

  // Debug : Affiche le vrai payload envoyé
  console.log('Payload build avant submit:', payload)

  try {
    await axios.post('/api/builds', payload)
    router.visit('/')
  } catch (error) {
    console.error('Erreur lors de la création du build:', error)
    alert('Erreur lors de la création du build.')
  }
}
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-10 space-y-10">
    <div class="bg-white p-6 rounded-xl shadow-md border space-y-4">
      <h1 class="text-2xl font-bold text-darknavy">Créer un build</h1>
      <BuildFormFields
        v-model:name="build.name"
        v-model:description="build.description"
        v-model:imgUrl="build.imgUrl"
        :auto-price="autoPrice"
      />
    </div>

    <div class="bg-white p-4 rounded-xl shadow-md border">
      <table class="w-full text-sm">
        <thead class="text-darknavy font-semibold bg-lightgray">
          <tr>
            <th class="text-left px-4 py-2">Composant</th>
            <th class="text-left px-4 py-2">Sélection</th>
            <th class="text-left px-4 py-2">Prix</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="type in componentTypes"
            :key="type.key"
            class="border-b odd:bg-lightgray even:bg-white"
          >
            <td class="px-4 py-3 font-medium">{{ type.label }}</td>
            <td class="px-4 py-3 flex items-center gap-3">
              <span class="text-darknavy font-medium">
                {{ build[type.key]?.name ?? 'Aucun sélectionné' }}
              </span>
              <button
                class="bg-primary hover:bg-cyan text-white text-xs px-3 py-1 rounded-xl"
                @click="handleSelect(type.key)"
              >
                {{ build[type.key] ? 'Changer' : '+ Ajouter' }}
              </button>
            </td>
            <td class="px-4 py-3 text-darkgray">
              {{
                build[type.key]?.price !== undefined
                  ? `${build[type.key].price} €`
                  : '—'
              }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div
      v-if="selectorKey && componentTypes.find(t => t.key === selectorKey)?.endpoint"
      class="bg-white border shadow-md rounded-xl p-4"
    >
      <ComponentSelectorTable
        :endpoint="componentTypes.find(t => t.key === selectorKey)?.endpoint"
        @select="(item) => {
          build[selectorKey] = item
          selectorKey = null
        }"
      />
    </div>

    <div class="flex justify-end">
      <button
        class="bg-darknavy text-white px-6 py-2 rounded-xl hover:bg-violetdark transition"
        @click="submitBuild"
      >
        💾 Créer le build
      </button>
    </div>
  </div>
</template>
