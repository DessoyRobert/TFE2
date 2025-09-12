<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useBuildStore } from '@/stores/buildStore'
import { useCartStore } from '@/stores/cartStore'
import ComponentCard from '@/Components/ComponentCard.vue'
import axios from 'axios'

// ---------- Utils ----------
function createIdempotencyKey () {
  return (crypto?.randomUUID?.() || `${Date.now()}-${Math.random()}`)
}
function normalizeIds(arr) {
  return (Array.isArray(arr) ? arr : [])
    .map(v => (typeof v === 'number' || /^\d+$/.test(String(v))) ? Number(v) : null)
    .filter(Boolean)
}

// ---------- UI état / modale ----------
const showModal = ref(false)
const modalMessage = ref('')

function onModalClose() {
  showModal.value = false
  router.visit('/builds')
}

// ---------- Stores ----------
const buildStore = useBuildStore()
const cart = useCartStore()

// ---------- Données / catégories ----------
const categories = [
  { key: 'cpu',          label: 'Processeur',       endpoint: '/api/cpus' },
  { key: 'gpu',          label: 'Carte Graphique',  endpoint: '/api/gpus' },
  { key: 'ram',          label: 'Mémoire RAM',      endpoint: '/api/rams' },
  { key: 'motherboard',  label: 'Carte Mère',       endpoint: '/api/motherboards' },
  { key: 'storage',      label: 'Stockage',         endpoint: '/api/storages' },
  { key: 'psu',          label: 'Alimentation',     endpoint: '/api/psus' },
  { key: 'cooler',       label: 'Refroidissement',  endpoint: '/api/coolers' },
  { key: 'case_model',   label: 'Boîtier',          endpoint: '/api/case-models' },
]

const selectedCategory = ref(categories[0])
const items = ref([])
const loading = ref(false)

// --------- Recherche locale (par catégorie) ---------
const search = ref('')
watch(selectedCategory, () => { search.value = '' }) // reset quand on change de type

function textHaystack(it) {
  const name  = (it?.name ?? '').toString()
  const brand = typeof it?.brand === 'string' ? it.brand : (it?.brand?.name ?? '')
  const type  = it?.type?.name ?? it?.type ?? ''
  return `${name} ${brand} ${type}`.toLowerCase()
}

const filteredItems = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return visibleItems.value
  return visibleItems.value.filter(it => textHaystack(it).includes(q))
})

// ---------- Chargement ----------
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
    const res  = await fetch(category.endpoint, { headers: { Accept: 'application/json' } })
    const text = await res.text()
    let data; try { data = JSON.parse(text) } catch { data = text }

    if (!res.ok) {
      const msg = typeof data === 'string' ? data : (data?.message || JSON.stringify(data))
      throw new Error(`${res.status} ${msg}`)
    }

    items.value = Array.isArray(data) ? data
      : Array.isArray(data?.data) ? data.data
      : Array.isArray(data?.items) ? data.items
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

// ---------- Totaux / validations ----------
const totalPrice = computed(() =>
  Object.values(buildStore.build)
    .filter(Boolean)
    .reduce((sum, comp) => sum + (parseFloat(comp.price) || 0), 0)
)

const isBuildEmpty = computed(() =>
  Object.values(buildStore.build).filter(Boolean).length === 0
)

const missingList = computed(() =>
  typeof buildStore.missingRequired === 'function' ? buildStore.missingRequired() : []
)

const disableSave = computed(() => buildStore.errors.length > 0 || isBuildEmpty.value)

const disableCheckout = computed(() =>
  buildStore.errors.length > 0 ||
  !(typeof buildStore.isValid === 'function' ? buildStore.isValid() : !isBuildEmpty.value)
)

// Filtrage par compatibilité
const visibleItems = computed(() => {
  const key = selectedCategory.value?.key
  if (!key) return items.value
  const allowed = buildStore.compatibility?.[key]
  if (!Array.isArray(allowed) || allowed.length === 0) return items.value
  return items.value.filter(it => {
    const id = it.id ?? it.component_id
    return allowed.includes(id)
  })
})

// ---------- Helper: construire component_ids pour l'API builds ----------
function getComponentIds () {
  return normalizeIds(
    Object.values(buildStore.build)
      .filter(Boolean)
      .map(c => c.id ?? c.component_id)
  )
}

// ---------- Sauvegarder (API JSON) ----------
const savingBuild = ref(false)

async function saveBuild() {
  if (disableSave.value || savingBuild.value) return
  savingBuild.value = true
  try {
    const component_ids = getComponentIds()
    if (!component_ids.length) throw new Error('Aucun composant sélectionné.')

    const idempotencyKey = createIdempotencyKey()
    const { data } = await axios.post(
      '/api/builds',
      {
        name: 'Build personnalisé',
        description: '',
        img_url: null,
        component_ids,
      },
      { headers: { 'Idempotency-Key': idempotencyKey } }
    )

    if (data?.ok && data?.build_id) {
      modalMessage.value = 'Build sauvegardé avec succès.'
      showModal.value = true
    } else {
      throw new Error(data?.message || 'Sauvegarde non confirmée.')
    }
  } catch (e) {
    console.error('SAVE ERROR', e?.response?.data || e)
    const msg = e?.response?.data?.message || e?.message || 'Impossible de sauvegarder le build.'
    modalMessage.value = msg
    showModal.value = true
  } finally {
    savingBuild.value = false
  }
}

// ---------- Sauvegarder & Commander ----------
const savingAndCheckout = ref(false)

async function saveAndCheckout() {
  if (disableCheckout.value || savingAndCheckout.value) return
  savingAndCheckout.value = true

  try {
    const component_ids = getComponentIds()
    if (!component_ids.length) throw new Error('Aucun composant sélectionné.')

    const idemBuild = createIdempotencyKey()
    const { data } = await axios.post(
      '/api/builds',
      { name: 'Build personnalisé', description: '', img_url: null, component_ids },
      { headers: { 'Idempotency-Key': idemBuild, Accept: 'application/json' } }
    )

    if (!data?.ok || !data?.build_id) {
      throw new Error(data?.message || 'Impossible de sauvegarder le build.')
    }

    if (data.redirect_url) {
      window.location.replace(data.redirect_url)            // ex: /checkout?build=123
    } else {
      router.visit(`/checkout?build=${data.build_id}`)      // fallback
    }
  } catch (e) {
    console.error('SAVE&CHECKOUT ERROR', e?.response?.data || e)
    const status = e?.response?.status
    if (status === 401) {
      modalMessage.value = 'Connecte-toi pour passer commande.'
    } else if (status === 422) {
      modalMessage.value = e?.response?.data?.message || 'Données invalides.'
    } else {
      modalMessage.value = e?.response?.data?.message || e?.message || 'Impossible de sauvegarder & commander.'
    }
    showModal.value = true
  } finally {
    savingAndCheckout.value = false
  }
}


// ---------- Responsive panels (mobile) ----------
const openCats = ref(false)
const openSummary = ref(false)
function closePanels() { openCats.value = false; openSummary.value = false }
function onKeydown(e) { if (e.key === 'Escape') closePanels() }
function onResize() {
  if (window.matchMedia('(min-width: 768px)').matches) closePanels()
}

// ---------- Montage ----------
onMounted(async () => {
  window.addEventListener('keydown', onKeydown)
  window.addEventListener('resize', onResize)

  // Recréer depuis sessionStorage (si on vient de "Recréer")
  try {
    const rawBuild = sessionStorage.getItem('rebuild_build')
    if (rawBuild) {
      const full = JSON.parse(rawBuild)
      if (typeof buildStore.reset === 'function') buildStore.reset()
      if (typeof buildStore.fillFromBuild === 'function') buildStore.fillFromBuild(full)
      sessionStorage.removeItem('rebuild_build')
    } else {
      const raw = sessionStorage.getItem('rebuild_payload')
      if (raw) {
        const arr = JSON.parse(raw) || []
        if (typeof buildStore.reset === 'function') buildStore.reset()
        for (const c of arr) {
          const key = typeof buildStore.normalizeType === 'function'
            ? buildStore.normalizeType(c.type_key)
            : (c.type_key || '')
          if (!key) continue
          if (typeof buildStore.addComponent === 'function') {
            buildStore.addComponent(key, {
              id: c.id, component_id: c.id, name: c.name, price: c.price,
            })
          } else if (buildStore.build && Object.prototype.hasOwnProperty.call(buildStore.build, key)) {
            buildStore.build[key] = { id: c.id, name: c.name, price: c.price }
          }
        }
        sessionStorage.removeItem('rebuild_payload')
      }
    }
  } catch (e) {
    console.warn('Hydratation Recréer: payload invalide', e)
    sessionStorage.removeItem('rebuild_build')
    sessionStorage.removeItem('rebuild_payload')
  }

  if (typeof buildStore.validateBuild === 'function') buildStore.validateBuild()
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeydown)
  window.removeEventListener('resize', onResize)
})

// Déclenche la validation à chaque changement du build
watch(
  () => buildStore.build,
  () => {
    buildStore.validateBuild?.()
    setTimeout(() => {
      if (buildStore.errors.length) {
        document.getElementById('validation-panel')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
      }
    }, 400)
  },
  { deep: true }
)

// Chargement initial
loadItems(selectedCategory.value)
</script>

<template>
  <div class="min-h-screen bg-gray-100 pt-16">
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
        <aside class="hidden md:block w-64 bg-darknavy text-white p-4 space-y-2 rounded-xl sticky top-24 self-start">
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
          <div class="flex items-end justify-between gap-3 mb-4 flex-wrap">
            <h2 class="text-2xl font-bold">{{ selectedCategory.label }}</h2>

            <!-- 🔎 Recherche locale -->
            <div class="relative w-full sm:w-80">
              <label for="searchBox" class="sr-only">Rechercher</label>
              <input
                id="searchBox"
                v-model="search"
                type="text"
                :placeholder="`Rechercher un(e) ${selectedCategory.label} (nom, marque…)`"
                class="w-full pl-10 pr-10 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring focus:ring-cyan-300 bg-white"
                autocomplete="off"
              />
              <!-- Icone -->
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none">
                <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  d="m21 21-4.3-4.3M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" />
              </svg>
              <!-- Clear -->
              <button
                v-if="search"
                @click="search = ''"
                class="absolute right-2 top-1/2 -translate-y-1/2 px-2 text-slate-500 hover:text-slate-700"
                aria-label="Effacer la recherche"
              >✕</button>
              <p class="mt-1 text-xs text-slate-500">
                {{ filteredItems.length }} résultat{{ filteredItems.length > 1 ? 's' : '' }}
              </p>
            </div>
          </div>

          <!-- Messages validation -->
          <div id="validation-panel" class="space-y-2 mb-4">
            <div v-if="buildStore.validating" class="text-sm text-gray-600">Validation en cours…</div>

            <div v-if="missingList.length" class="rounded-lg bg-blue-50 border border-blue-200 p-3 text-blue-900">
              <p class="font-semibold mb-1">Éléments requis manquants</p>
              <ul class="list-disc pl-5">
                <li v-for="m in missingList" :key="m">{{ m }}</li>
              </ul>
            </div>

            <div v-if="buildStore.errors.length" class="rounded-lg bg-red-50 border border-red-200 p-3 text-red-800">
              <p class="font-semibold mb-1">Erreurs de compatibilité</p>
              <ul class="list-disc pl-5">
                <li v-for="(e,i) in buildStore.errors" :key="'err-'+i">{{ e }}</li>
              </ul>
            </div>

            <div v-if="buildStore.warnings.length" class="rounded-lg bg-yellow-50 border border-yellow-200 p-3 text-yellow-800">
              <p class="font-semibold mb-1">Avertissements</p>
              <ul class="list-disc pl-5">
                <li v-for="(w,i) in buildStore.warnings" :key="'warn-'+i">{{ w }}</li>
              </ul>
            </div>
          </div>

          <div v-if="loading">Chargement...</div>

          <!-- Grille -->
          <div v-else>
            <div v-if="!filteredItems.length" class="p-6 text-center text-slate-500 bg-white rounded-xl border">
              Aucun résultat pour « {{ search }} ». Essaie un autre terme ou change de catégorie.
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              <ComponentCard
                v-for="item in filteredItems"
                :key="item.id"
                :item="item"
                :disableSelect="!buildStore.isCompatible(selectedCategory.key, item.id ?? item.component_id)"
                :show-details="true"
                :show-add-to-cart="false"
                @select="selectComponent"
              />
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
            <div v-if="buildStore.validating" class="text-sm text-gray-600">Validation en cours…</div>

            <div v-if="buildStore.errors.length" class="rounded-lg bg-red-50 border border-red-200 p-3 text-red-800">
              <p class="font-semibold mb-1">Erreurs de compatibilité</p>
              <ul class="list-disc pl-5">
                <li v-for="(e,i) in buildStore.errors" :key="'err2-'+i">{{ e }}</li>
              </ul>
            </div>

            <div v-if="buildStore.warnings.length" class="rounded-lg bg-yellow-50 border border-yellow-200 p-3 text-yellow-800">
              <p class="font-semibold mb-1">Avertissements</p>
              <ul class="list-disc pl-5">
                <li v-for="(w,i) in buildStore.warnings" :key="'warn2-'+i">{{ w }}</li>
              </ul>
            </div>
          </div>

          <div class="flex flex-col gap-2 mt-auto w-full">
            <button
              @click="saveBuild"
              :disabled="disableSave || savingBuild"
              :class="[
                'w-full py-2 rounded-xl transition-all',
                (disableSave || savingBuild)
                  ? 'bg-gray-400 cursor-not-allowed text-white'
                  : 'bg-green-600 hover:bg-green-700 text-white'
              ]"
            >
              {{ savingBuild ? 'Sauvegarde…' : 'Sauvegarder le build' }}
            </button>

            <button
              @click="saveAndCheckout"
              :disabled="disableCheckout || savingAndCheckout"
              :class="[
                'w-full py-2 rounded-xl transition-all',
                (disableCheckout || savingAndCheckout)
                  ? 'bg-gray-300 cursor-not-allowed text-white'
                  : 'bg-emerald-600 hover:bg-emerald-700 text-white'
              ]"
            >
              {{ savingAndCheckout ? 'Traitement…' : 'Sauvegarder & Commander' }}
            </button>
          </div>
        </aside>
      </div>
    </div>

    <!-- Overlay pour panneaux mobiles -->
    <div
      v-show="openCats || openSummary"
      class="fixed inset-0 bg-black/40 md:hidden"
      @click="closePanels"
    ></div>

    <!-- Panneau mobile : Catégories -->
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

    <!-- Panneau mobile : Résumé -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="translate-x-full opacity-0"
      enter-to-class="translate-x-0 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="translate-x-0 opacity-0"
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

        <div class="mt-auto w-full flex flex-col gap-2">
          <button
            @click="saveBuild"
            :disabled="disableSave || savingBuild"
            :class="[
              'w-full py-2 rounded-xl transition-all',
              (disableSave || savingBuild)
                ? 'bg-gray-400 cursor-not-allowed text-white'
                : 'bg-green-600 hover:bg-green-700 text-white'
            ]"
          >
            {{ savingBuild ? 'Sauvegarde…' : 'Sauvegarder le build' }}
          </button>

          <button
            @click="saveAndCheckout"
            :disabled="disableCheckout || savingAndCheckout"
            :class="[
              'w-full py-2 rounded-xl transition-all',
              (disableCheckout || savingAndCheckout)
                ? 'bg-gray-300 cursor-not-allowed text-white'
                : 'bg-emerald-600 hover:bg-emerald-700 text-white'
            ]"
          >
            {{ savingAndCheckout ? 'Traitement…' : 'Sauvegarder & Commander' }}
          </button>
        </div>
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
