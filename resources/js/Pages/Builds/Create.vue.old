<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useBuildStore } from '@/stores/buildStore'
import { useBuildValidatorStore } from '@/stores/useBuildValidatorStore'
import { router } from '@inertiajs/vue3'

// Composants
import BuildFormFields from '@/Components/BuildFormFields.vue'
import ComponentSelectorTable from '@/Components/ComponentSelectorTable.vue'

const buildStore = useBuildStore()
const validatorStore = useBuildValidatorStore()

// Champs du build (liaison avec le store si tu veux deux-way binding)
const buildName = ref(buildStore.buildName)
const buildDescription = ref(buildStore.buildDescription)
const buildImgUrl = ref(buildStore.buildImgUrl)

// Liste des types de composants
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

// Sync les champs texte avec le store quand on tape dedans
watch(buildName, val => buildStore.buildName = val)
watch(buildDescription, val => buildStore.buildDescription = val)
watch(buildImgUrl, val => buildStore.buildImgUrl = val)

// À l’arrivée sur la page, si tout est vide dans le store → reset tout
onMounted(() => {
  const allEmpty = componentTypes.every(type => !buildStore.build[type.key])
  if (allEmpty) {
    buildStore.reset()
    buildName.value = ''
    buildDescription.value = ''
    buildImgUrl.value = ''
  } else {
    // Restaure le nom/description depuis le store
    buildName.value = buildStore.buildName
    buildDescription.value = buildStore.buildDescription
    buildImgUrl.value = buildStore.buildImgUrl
  }
})

// Calcul automatique du prix
const autoPrice = computed(() => {
  return componentTypes.reduce((total, t) => {
    const comp = buildStore.build[t.key]
    return total + (comp?.price ? Number(comp.price) : 0)
  }, 0)
})

// Sélection composant (modal)
const selectorKey = ref(null)
function handleSelect(key) {
  selectorKey.value = selectorKey.value === key ? null : key
}
function removeComponent(key) {
  buildStore.build[key] = null
}
function resetBuild() {
  buildStore.reset()
  buildName.value = ''
  buildDescription.value = ''
  buildImgUrl.value = ''
}

// Soumission du build
async function submitBuild() {
  const payload = {
    name: buildName.value,
    description: buildDescription.value,
    img_url: buildImgUrl.value,
    price: autoPrice.value,
    components: [],
  }
  for (const type of componentTypes) {
    const comp = buildStore.build[type.key]
    if (comp && (comp.component_id || comp.id)) {
      payload.components.push({
        component_id: comp.component_id ?? comp.id,
      })
    }
  }
  try {
    await axios.post('/api/builds', payload)
    alert('Build créé avec succès !')
    router.visit('/')
  } catch (error) {
    console.error('Erreur lors de la création du build:', error)
    alert('Erreur lors de la création du build.')
  }
}

// Validation dynamique
const selectedComponentIds = computed(() =>
  componentTypes
    .map(type => buildStore.build[type.key]?.component_id ?? buildStore.build[type.key]?.id)
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
    <div class="bg-white p-6 rounded-xl shadow-md border space-y-4">
      <h1 class="text-2xl font-bold text-darknavy">Créer un build</h1>
      <BuildFormFields
        v-model:name="buildName"
        v-model:description="buildDescription"
        v-model:imgUrl="buildImgUrl"
        :auto-price="autoPrice"
      />
    </div>

    <div v-if="validatorStore.validating" class="text-gray-600 py-2">Validation en cours...</div>
    <ul v-if="validatorStore.errors.length" class="bg-red-100 text-red-800 rounded-xl p-3 my-2">
      <li v-for="(err, i) in validatorStore.errors" :key="i">Erreur : {{ err }}</li>
    </ul>
    <ul v-if="validatorStore.warnings.length" class="bg-yellow-100 text-yellow-800 rounded-xl p-3 my-2">
      <li v-for="(warn, i) in validatorStore.warnings" :key="i">Avertissement : {{ warn }}</li>
    </ul>

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
                {{ buildStore.build[type.key]?.name ?? 'Aucun sélectionné' }}
              </span>
              <button
                class="bg-primary hover:bg-cyan text-white text-xs px-3 py-1 rounded-xl"
                @click="handleSelect(type.key)"
              >
                {{ buildStore.build[type.key] ? 'Changer' : '+ Ajouter' }}
              </button>
              <button
                v-if="buildStore.build[type.key]"
                @click="removeComponent(type.key)"
                class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded-xl"
              >
                Supprimer
              </button>
            </td>
            <td class="px-4 py-3 text-darkgray">
              {{ buildStore.build[type.key]?.price !== undefined ? `${buildStore.build[type.key].price} €` : '—' }}
            </td>
          </tr>
        </tbody>
      </table>
      <div class="text-right text-lg font-bold text-darknavy mt-4">
        Prix total : {{ autoPrice }} €
      </div>
    </div>

    <!-- Sélecteur modal -->
    <Teleport to="body">
      <transition name="fade">
        <div
          v-if="selectorKey && componentTypes.find(t => t.key === selectorKey)?.endpoint"
          class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
          <div class="bg-white p-6 rounded-xl shadow-lg w-full max-w-4xl relative animate-fade-in">
            <button
              class="absolute top-4 right-4 text-gray-500 hover:text-black text-lg"
              @click="selectorKey = null"
            >
              ✕
            </button>
            <ComponentSelectorTable
              :endpoint="componentTypes.find(t => t.key === selectorKey)?.endpoint"
              @select="(item) => {
                buildStore.build[selectorKey] = item
                selectorKey = null
              }"
            />
          </div>
        </div>
      </transition>
    </Teleport>

    <div class="flex justify-between mt-6">
      <button
        @click="resetBuild"
        class="bg-red-600 text-white px-6 py-2 rounded-xl hover:bg-red-700 transition"
      >
        Réinitialiser le build
      </button>

      <button
        @click="submitBuild"
        class="bg-darknavy text-white px-6 py-2 rounded-xl hover:bg-violetdark transition"
        :disabled="selectedComponentIds.length < componentTypes.length"
        :class="{ 'opacity-50 cursor-not-allowed': selectedComponentIds.length < componentTypes.length }"
      >
        Créer le build
      </button>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
