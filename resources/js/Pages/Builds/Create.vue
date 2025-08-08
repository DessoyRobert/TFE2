<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useBuildStore } from '@/stores/buildStore'

// Modal confirmation
const showModal = ref(false)
const modalMessage = ref('')

// Fonction fermeture modal + redirection
function onModalClose() {
  showModal.value = false
  router.visit(route('builds.index'))
}

// Store global build
const buildStore = useBuildStore()

// Validation erreurs et warnings
const errors = ref([])
const warnings = ref([])
const validating = ref(false)
let validateTimer = null

async function validateBuild() {
  validating.value = true

  const ids = Object.values(buildStore.build)
    .filter(Boolean)
    .map(c => c.id ?? c.component_id)

  if (!ids.length) {
    errors.value = []
    warnings.value = []
    validating.value = false
    return
  }

  try {
    const res = await fetch('/api/builds/validate-temp', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ component_ids: ids })
    })

    const data = await res.json()
    errors.value = data.errors || []
    warnings.value = data.warnings || []
  } catch (e) {
    console.error('Erreur validation build', e)
    errors.value = ['Validation indisponible. Réessaie plus tard.']
    warnings.value = []
  }
  validating.value = false
}

watch(
  () => buildStore.build,
  () => {
    clearTimeout(validateTimer)
    validateTimer = setTimeout(() => {
      validateBuild()
      setTimeout(() => {
        if (errors.value.length) {
          document
            .getElementById('validation-panel')
            ?.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
        }
      }, 0)
    }, 300)
  },
  { deep: true }
)

const categories = [
  { key: 'cpu', label: 'Processeur', endpoint: '/api/cpus' },
  { key: 'gpu', label: 'Carte Graphique', endpoint: '/api/gpus' },
  { key: 'ram', label: 'Mémoire RAM', endpoint: '/api/rams' },
  { key: 'motherboard', label: 'Carte Mère', endpoint: '/api/motherboards' },
  { key: 'storage', label: 'Stockage', endpoint: '/api/storages' },
  { key: 'psu', label: 'Alimentation', endpoint: '/api/psus' },
  { key: 'cooler', label: 'Refroidissement', endpoint: '/api/coolers' },
  { key: 'case_model', label: 'Boîtier', endpoint: '/api/case-models' }
]

const selectedCategory = ref(categories[0])
const items = ref([])
const loading = ref(false)

const eur = new Intl.NumberFormat('fr-BE', { style: 'currency', currency: 'EUR' })

function pickImageUrl(item) {
  const url =
    item?.images?.[0]?.url ??
    item?.component?.images?.[0]?.url ??
    item?.component?.img_url ??
    item?.img_url ??
    null

  if (!url) return '/images/placeholder-component.svg'
  if (url.includes('/upload/')) {
    return url.replace('/upload/', '/upload/f_auto,q_auto,w_480/')
  }
  return url
}

async function loadItems(category) {
  selectedCategory.value = category
  loading.value = true
  items.value = []
  try {
    const res = await fetch(category.endpoint, { headers: { Accept: 'application/json' } })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    const json = await res.json()
    items.value = Array.isArray(json)
      ? json
      : Array.isArray(json.data)
      ? json.data
      : Array.isArray(json.items)
      ? json.items
      : []
  } catch (e) {
    console.error('Erreur chargement composants :', e)
  } finally {
    loading.value = false
  }
}

function selectComponent(component) {
  buildStore.build[selectedCategory.value.key] = component
}
function removeComponent(key) {
  buildStore.build[key] = null
}

const totalPrice = computed(() => {
  return Object.values(buildStore.build)
    .filter(Boolean)
    .reduce((sum, comp) => sum + (parseFloat(comp.price) || 0), 0)
})

const isBuildEmpty = computed(() =>
  Object.values(buildStore.build).filter(Boolean).length === 0
)

const disableSave = computed(() =>
  errors.value.length > 0 || isBuildEmpty.value
)

function saveBuild() {
  if (disableSave.value) return

  const componentsPayload = Object.values(buildStore.build)
    .filter(Boolean)
    .map(c => ({ component_id: c.id ?? c.component_id }))

  router.post('/builds', {
    name: 'Build personnalisé',
    description: '',
    price: totalPrice.value.toFixed(2),
    components: componentsPayload,
  }, {
    onSuccess: () => {
      modalMessage.value = 'Build sauvegardé avec succès !'
      showModal.value = true
    },
    onError: () => {
      modalMessage.value = 'Erreur lors de la sauvegarde.'
      showModal.value = true
    }
  })
}

// Chargement initial
loadItems(selectedCategory.value)
</script>

<template>
  <div class="flex min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-1/5 bg-darknavy text-white p-4 space-y-2">
      <h2 class="text-lg font-bold mb-4">Catégories</h2>
      <ul class="space-y-2">
        <li v-for="cat in categories" :key="cat.key">
          <button
            @click="loadItems(cat)"
            :class="[
              'w-full text-left px-3 py-2 rounded transition-all',
              cat.key === selectedCategory.key ? 'bg-primary' : 'hover:bg-gray-700'
            ]"
          >
            {{ cat.label }}
          </button>
        </li>
      </ul>
    </aside>

    <!-- Zone centrale -->
    <main class="flex-1 p-6">
      <h2 class="text-2xl font-bold mb-4">{{ selectedCategory.label }}</h2>
      <div v-if="loading">Chargement...</div>

      <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="item in items"
          :key="item.id"
          class="bg-white rounded-xl shadow-md p-4 flex flex-col justify-between"
        >
          <Link
            :href="route('components.details', item.id)"
            class="block group"
            aria-label="Voir le détail du composant"
          >
            <img
              :src="pickImageUrl(item)"
              :alt="item.name"
              width="480"
              height="240"
              class="w-full h-32 object-contain mb-2 transition-transform group-hover:scale-[1.02]"
              loading="lazy"
              decoding="async"
            />
            <h3 class="font-semibold group-hover:text-primary transition-colors">
              {{ item.name }}
            </h3>
            <p class="text-gray-600">{{ item.price }} €</p>
          </Link>

          <button
            @click="selectComponent(item)"
            class="mt-3 bg-primary hover:bg-cyan text-white px-3 py-2 rounded-lg"
          >
            Sélectionner
          </button>
        </div>
      </div>
    </main>

    <!-- Résumé -->
    <aside class="w-1/4 bg-white shadow-lg p-4 border-l">
      <h2 class="text-xl font-bold mb-4">Résumé du Build</h2>

      <div v-if="isBuildEmpty" class="text-gray-500 italic text-center py-4">
        Aucun composant sélectionné.
      </div>

      <ul v-else class="space-y-2">
        <li
          v-for="cat in categories"
          :key="cat.key"
          class="flex justify-between items-center border-b pb-2"
        >
          <span>{{ cat.label }}</span>
          <template v-if="buildStore.build[cat.key]">
            <span>{{ buildStore.build[cat.key].name }}</span>
            <button
              @click="removeComponent(cat.key)"
              class="text-red-500 hover:underline text-sm"
            >
              Retirer
            </button>
          </template>
          <template v-else>
            <span class="text-gray-400">Non choisi</span>
          </template>
        </li>
      </ul>

      <div class="mt-4 font-bold text-lg">
        Total : {{ totalPrice.toFixed(2) }} €
      </div>

      <div v-if="errors.length" id="validation-panel" class="mt-4 bg-red-100 p-2 rounded">
        <strong>Erreurs :</strong>
        <ul class="list-disc ml-5">
          <li v-for="(e, i) in errors" :key="i">{{ e }}</li>
        </ul>
      </div>

      <div v-else-if="warnings.length" class="mt-4 bg-yellow-100 p-2 rounded">
        <strong>Avertissements :</strong>
        <ul class="list-disc ml-5">
          <li v-for="(w, i) in warnings" :key="i">{{ w }}</li>
        </ul>
      </div>

      <button
        @click="saveBuild"
        :disabled="disableSave"
        :class="[
          'mt-4 w-full py-2 rounded-xl transition-all',
          disableSave
            ? 'bg-gray-400 cursor-not-allowed text-white'
            : 'bg-green-600 hover:bg-green-700 text-white'
        ]"
      >
        Sauvegarder le build
      </button>
    </aside>
  </div>

  <!-- Modal simple -->
  <div
    v-if="showModal"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"
  >
    <div class="bg-white rounded p-6 max-w-sm text-center">
      <p class="mb-4">{{ modalMessage }}</p>
      <button
        @click="onModalClose"
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
      >
        OK
      </button>
    </div>
  </div>
</template>
