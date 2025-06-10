<script setup>
// Imports Vue
import { ref, computed, watch } from 'vue'
import axios from 'axios'

// Inertia router (navigation SPA)
import { router } from '@inertiajs/vue3'

// Pinia store pour les validations dynamiques
import { useBuildValidatorStore } from '@/stores/useBuildValidatorStore'

// Composants internes
import BuildFormFields from '@/Components/BuildFormFields.vue'
import ComponentSelectorTable from '@/Components/ComponentSelectorTable.vue'

// Store de validation
const validatorStore = useBuildValidatorStore()

// Structure du build en cours de création
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

// Calcul automatique du prix total du build
const autoPrice = computed(() => {
  return componentTypes.reduce((total, t) => {
    const comp = build.value[t.key]
    return total + (comp?.price ? Number(comp.price) : 0)
  }, 0)
})

// Gestion de l'ouverture/fermeture du sélecteur de composants
const selectorKey = ref(null)

function handleSelect(key) {
  selectorKey.value = selectorKey.value === key ? null : key
}

// Envoi du build via l’API
async function submitBuild() {
  const payload = {
    name: build.value.name,
    description: build.value.description,
    imgUrl: build.value.imgUrl,
    price: autoPrice.value,
    components: [],
  }

  for (const type of componentTypes) {
    const comp = build.value[type.key]
    if (comp && comp.component_id) {
      payload.components.push({ component_id: comp.component_id })
    }
  }

  try {
    await axios.post('/api/builds', payload)
    router.visit('/')
  } catch (error) {
    console.error('Erreur lors de la création du build:', error)
    alert('Erreur lors de la création du build.')
  }
}

// Validation dynamique (via store)
const selectedComponentIds = computed(() =>
  componentTypes
    .map(type => build.value[type.key]?.component_id)
    .filter(Boolean)
)

watch(selectedComponentIds, (ids) => {
  if (ids.length > 0) {
    validatorStore.validateBuild(ids)
  } else {
    validatorStore.errors = []
    validatorStore.warnings = []
  }
})
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-10 space-y-10">
    <!-- Informations générales -->
    <div class="bg-white p-6 rounded-xl shadow-md border space-y-4">
      <h1 class="text-2xl font-bold text-darknavy">Créer un build</h1>
      <BuildFormFields
        v-model:name="build.name"
        v-model:description="build.description"
        v-model:imgUrl="build.imgUrl"
        :auto-price="autoPrice"
      />
    </div>

    <!-- Résultats des validations -->
    <div v-if="validatorStore.validating" class="text-gray-600 py-2">Validation en cours...</div>
    <ul v-if="validatorStore.errors.length" class="bg-red-100 text-red-800 rounded-xl p-3 my-2">
      <li v-for="(err, i) in validatorStore.errors" :key="i">Erreur : {{ err }}</li>
    </ul>
    <ul v-if="validatorStore.warnings.length" class="bg-yellow-100 text-yellow-800 rounded-xl p-3 my-2">
      <li v-for="(warn, i) in validatorStore.warnings" :key="i">Avertissement : {{ warn }}</li>
    </ul>

    <!-- Tableau des composants sélectionnés -->
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

    <!-- Bouton de création -->
    <div class="flex justify-end">
      <button
        class="bg-darknavy text-white px-6 py-2 rounded-xl hover:bg-violetdark transition"
        @click="submitBuild"
      >
        Créer le build
      </button>
    </div>
  </div>
</template>
