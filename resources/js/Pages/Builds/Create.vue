<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useBuildStore } from '@/stores/buildStore'

// Modal confirmation
const showModal = ref(false)
const modalMessage = ref('')

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

// Responsive panels (mobile)
const openCats = ref(false)
const openSummary = ref(false)
function closePanels() { openCats.value = false; openSummary.value = false }
function onKeydown(e) { if (e.key === 'Escape') closePanels() }
function onResize() {
  if (window.matchMedia('(min-width: 768px)').matches) closePanels()
}

onMounted(() => {
  window.addEventListener('keydown', onKeydown)
  window.addEventListener('resize', onResize)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeydown)
  window.removeEventListener('resize', onResize)
})

// Chargement initial
loadItems(selectedCategory.value)
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <div class="max-w-8xl mx-auto p-4 md:p-6">
    <!-- Barre mobile sticky -->
    <div
      class="md:hidden sticky top-16 z-30 -mx-4 px-4 pt-2 pb-3
            bg-gray-100/95 backdrop-blur supports-[backdrop-filter]:bg-gray-100/70"
    >
      <div class="flex gap-2">
        <button @click="openCats = true" class="flex-1 bg-darknavy text-white px-4 py-2 rounded-xl">
          Catégories
        </button>
        <button
          @click="openSummary = true"
          :disabled="isBuildEmpty"
          class="flex-1 bg-primary text-white px-4 py-2 rounded-xl disabled:opacity-60"
        >
          Résumé
        </button>
      </div>
    </div>


      <div class="flex gap-6">
        <!-- Sidebar catégories (desktop) -->
        <aside class="hidden md:block w-64 bg-darknavy text-white p-4 space-y-2 rounded-xl sticky top-24 self-start ">
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
        <main class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold mb-4">{{ selectedCategory.label }}</h2>

          <!-- Messages validation -->
          <div id="validation-panel" class="space-y-2 mb-4">
            <div v-if="validating" class="text-sm text-gray-600">Validation en cours…</div>

            <div v-if="errors.length" class="rounded-lg bg-red-50 border border-red-200 p-3 text-red-800">
              <p class="font-semibold mb-1">Erreurs de compatibilité</p>
              <ul class="list-disc pl-5">
                <li v-for="(e,i) in errors" :key="'err-'+i">{{ e }}</li>
              </ul>
            </div>

            <div v-if="warnings.length" class="rounded-lg bg-yellow-50 border border-yellow-200 p-3 text-yellow-800">
              <p class="font-semibold mb-1">Avertissements</p>
              <ul class="list-disc pl-5">
                <li v-for="(w,i) in warnings" :key="'warn-'+i">{{ w }}</li>
              </ul>
            </div>
          </div>

          <div v-if="loading">Chargement...</div>

          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
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

        <!-- Résumé (desktop) -->
        <aside class="hidden md:flex bg-white shadow-lg p-4 border-l rounded-xl sticky top-24 h-fit max-h-[80vh] flex-col">
          <h2 class="text-xl font-bold mb-4">Résumé du Build</h2>

          <div v-if="isBuildEmpty" class="text-gray-500 italic text-center py-4">
            Aucun composant sélectionné.
          </div>

          <ul class="space-y-2 flex-grow overflow-auto">
            <li
              v-for="cat in categories"
              :key="cat.key"
              class="flex justify-between items-center border-b pb-2"
            >
              <span>{{ cat.label }}</span>
              <template v-if="buildStore.build[cat.key]">
                <span class="truncate max-w-[55%]" :title="buildStore.build[cat.key].name">
                  {{ buildStore.build[cat.key].name }}
                </span>
                <button
                  @click="removeComponent(cat.key)"
                  class="text-red-500 hover:underline text-sm ml-4"
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

          <div class="mb-4 mt-4">
            <div v-if="validating" class="text-sm text-gray-600">Validation en cours…</div>

            <div v-if="errors.length" class="rounded-lg bg-red-50 border border-red-200 p-3 text-red-800">
              <p class="font-semibold mb-1">Erreurs de compatibilité</p>
              <ul class="list-disc pl-5">
                <li v-for="(e,i) in errors" :key="'err2-'+i">{{ e }}</li>
              </ul>
            </div>

            <div v-if="warnings.length" class="rounded-lg bg-yellow-50 border border-yellow-200 p-3 text-yellow-800">
              <p class="font-semibold mb-1">Avertissements</p>
              <ul class="list-disc pl-5">
                <li v-for="(w,i) in warnings" :key="'warn2-'+i">{{ w }}</li>
              </ul>
            </div>
          </div>

          <button
            @click="saveBuild"
            :disabled="disableSave"
            :class="[
              'mt-auto w-full py-2 rounded-xl transition-all',
              disableSave
                ? 'bg-gray-400 cursor-not-allowed text-white'
                : 'bg-green-600 hover:bg-green-700 text-white'
            ]"
          >
            Sauvegarder le build
          </button>
        </aside>
      </div>
    </div>

    <!-- Overlay pour panneaux mobiles -->
    <div
      v-show="openCats || openSummary"
      class="fixed inset-0 bg-black/40 md:hidden"
      @click="closePanels"
    ></div>

    <!-- Panneau mobile : Catégories (slide gauche) -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="-translate-x-full opacity-0"
      enter-to-class="translate-x-0 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="translate-x-0 opacity-100"
      leave-to-class="-translate-x-full opacity-0"
    >
      <aside
        v-show="openCats"
        class="fixed inset-y-0 left-0 z-40 w-80 max-w-[85%] bg-darknavy text-white p-4 space-y-2 rounded-r-2xl md:hidden"
      >
        <div class="flex items-center justify-between mb-2">
          <h2 class="text-lg font-bold">Catégories</h2>
          <button class="p-2 rounded hover:bg-white/10" @click="openCats = false" aria-label="Fermer">
            ✕
          </button>
        </div>
        <ul class="space-y-2">
          <li v-for="cat in categories" :key="cat.key">
            <button
              @click="loadItems(cat); openCats = false"
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
    </Transition>

    <!-- Panneau mobile : Résumé (slide droite) -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="translate-x-full opacity-0"
      enter-to-class="translate-x-0 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="translate-x-0 opacity-100"
      leave-to-class="translate-x-full opacity-0"
    >
      <aside
        v-show="openSummary"
        class="fixed inset-y-0 right-0 z-40 w-80 max-w-[85%] bg-white shadow-lg p-4 border-l rounded-l-2xl md:hidden flex flex-col"
      >
        <div class="flex items-center justify-between mb-2">
          <h2 class="text-lg font-bold">Résumé du Build</h2>
          <button class="p-2 rounded hover:bg-gray-100" @click="openSummary = false" aria-label="Fermer">
            ✕
          </button>
        </div>

        <div v-if="isBuildEmpty" class="text-gray-500 italic text-center py-4">
          Aucun composant sélectionné.
        </div>

        <ul class="space-y-2 flex-grow overflow-auto">
          <li
            v-for="cat in categories"
            :key="cat.key"
            class="flex justify-between items-center border-b pb-2"
          >
            <span>{{ cat.label }}</span>
            <template v-if="buildStore.build[cat.key]">
              <span class="truncate max-w-[55%]" :title="buildStore.build[cat.key].name">
                {{ buildStore.build[cat.key].name }}
              </span>
              <button
                @click="removeComponent(cat.key)"
                class="text-red-500 hover:underline text-sm ml-4"
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

        <div class="mb-4 mt-4">
          <div v-if="validating" class="text-sm text-gray-600">Validation en cours…</div>

          <div v-if="errors.length" class="rounded-lg bg-red-50 border border-red-200 p-3 text-red-800">
            <p class="font-semibold mb-1">Erreurs de compatibilité</p>
            <ul class="list-disc pl-5">
              <li v-for="(e,i) in errors" :key="'errm-'+i">{{ e }}</li>
            </ul>
          </div>

          <div v-if="warnings.length" class="rounded-lg bg-yellow-50 border border-yellow-200 p-3 text-yellow-800">
            <p class="font-semibold mb-1">Avertissements</p>
            <ul class="list-disc pl-5">
              <li v-for="(w,i) in warnings" :key="'warnm-'+i">{{ w }}</li>
            </ul>
          </div>
        </div>

        <button
          @click="() => { saveBuild(); }"
          :disabled="disableSave"
          :class="[
            'mt-auto w-full py-2 rounded-xl transition-all',
            disableSave
              ? 'bg-gray-400 cursor-not-allowed text-white'
              : 'bg-green-600 hover:bg-green-700 text-white'
          ]"
        >
          Sauvegarder le build
        </button>
      </aside>
    </Transition>

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
  </div>
</template>
