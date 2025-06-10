<script setup>
// Imports Vue
import { ref, computed } from 'vue'
import axios from 'axios'

// Inertia router
import { router } from '@inertiajs/vue3'

// Composants internes
import BuildFormFields from '@/Components/BuildFormFields.vue'
import ComponentSelectorTable from '@/Components/ComponentSelectorTable.vue'

// Définition des props reçues depuis Inertia
const props = defineProps({
  build: {
    type: Object,
    required: true
  }
})

// Initialisation des données à partir du build reçu
const build = ref({
  name: props.build.name || '',
  description: props.build.description || '',
  imgUrl: props.build.imgUrl || '',
  cpu: props.build.components.find(c => c.component_type_id === 1) || null,
  gpu: props.build.components.find(c => c.component_type_id === 2) || null,
  ram: props.build.components.find(c => c.component_type_id === 3) || null,
  motherboard: props.build.components.find(c => c.component_type_id === 4) || null,
  storage: props.build.components.find(c => c.component_type_id === 5) || null,
  psu: props.build.components.find(c => c.component_type_id === 6) || null,
  cooler: props.build.components.find(c => c.component_type_id === 7) || null,
  case_model: props.build.components.find(c => c.component_type_id === 8) || null,
})

// Définition des types de composants
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

// Calcul automatique du prix du build
const autoPrice = computed(() => {
  return componentTypes.reduce((total, t) => {
    const comp = build.value[t.key]
    return total + (comp?.price ? Number(comp.price) : 0)
  }, 0)
})

// Gestion du sélecteur de composant
const selectorKey = ref(null)

function handleSelect(key) {
  selectorKey.value = selectorKey.value === key ? null : key
}

// Envoi de la mise à jour du build
async function submitEdit() {
  const payload = {
    name: build.value.name,
    description: build.value.description,
    imgUrl: build.value.imgUrl,
    price: autoPrice.value,
    components: []
  }

  for (const type of componentTypes) {
    const comp = build.value[type.key]
    if (comp && comp.id) {
      payload.components.push({ component_id: comp.component_id ?? comp.id })
    }
  }

  try {
    await axios.put(`/api/builds/${props.build.id}`, payload)
    router.visit('/builds')
  } catch (error) {
    console.error('Erreur lors de la mise à jour du build:', error)
    alert('Erreur lors de la mise à jour du build.')
  }
}
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-10 space-y-10">
    <!-- Informations générales -->
    <div class="bg-white p-6 rounded-xl shadow-md border space-y-4">
      <h1 class="text-2xl font-bold text-darknavy">Éditer le build</h1>
      <BuildFormFields
        v-model:name="build.name"
        v-model:description="build.description"
        v-model:imgUrl="build.imgUrl"
        :auto-price="autoPrice"
      />
    </div>

    <!-- Tableau des composants -->
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
              {{ build[type.key]?.price !== undefined ? `${build[type.key].price} €` : '—' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Sélecteur de composant -->
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

    <!-- Boutons d'action -->
    <div class="flex justify-end space-x-2">
      <button
        class="bg-darknavy text-white px-6 py-2 rounded-xl hover:bg-violetdark transition"
        @click="submitEdit"
      >
        Enregistrer les modifications
      </button>
      <button
        class="bg-lightgray text-darknavy px-6 py-2 rounded-xl border"
        @click="$inertia.visit('/builds')"
      >
        Annuler
      </button>
    </div>
  </div>
</template>
