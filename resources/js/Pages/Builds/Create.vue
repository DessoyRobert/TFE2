<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useBuildStore } from '@/stores/buildStore' // shim -> pointe vers useBuildStore
import { useCartStore } from '@/stores/cartStore'
import ComponentCard from '@/Components/ComponentCard.vue'
// ---------- UI état / modale ----------
const showModal = ref(false)
const modalMessage = ref('')

function onModalClose() {
  showModal.value = false
  router.visit(route('builds.index'))
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
  { key: 'case_model',   label: 'Boîtier',          endpoint: '/api/case-models' }
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

// ---------- Totaux / validations ----------
const totalPrice = computed(() => {
  return Object.values(buildStore.build)
    .filter(Boolean)
    .reduce((sum, comp) => sum + (parseFloat(comp.price) || 0), 0)
})
const isBuildEmpty = computed(() =>
  Object.values(buildStore.build).filter(Boolean).length === 0
)

const missingList = computed(() => (typeof buildStore.missingRequired === 'function'
  ? buildStore.missingRequired()
  : []))

const disableSave = computed(() =>
  buildStore.errors.length > 0 || isBuildEmpty.value
)

const disableCheckout = computed(() =>
  buildStore.errors.length > 0 || !(
    typeof buildStore.isValid === 'function' ? buildStore.isValid() : !isBuildEmpty.value
  )
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

// ---------- Sauvegarder (web) ----------
const savingBuild = ref(false)
function saveBuild() {
  if (disableSave.value) return
  savingBuild.value = true

  // On utilise le payload du store mais on le convertit pour le contrôleur web:
  // toPayload() => { name, description, img_url, price, components:[{id,type}] }
  const payload = typeof buildStore.toPayload === 'function'
    ? buildStore.toPayload()
    : {
        name: 'Build personnalisé',
        description: '',
        img_url: null,
        price: Number(totalPrice.value.toFixed(2)),
        components: Object.values(buildStore.build)
          .filter(Boolean)
          .map(c => ({ id: c.id ?? c.component_id })),
      }

  // Format attendu côté BuildController (web): components: [{ component_id }]
  const componentsPayload = (payload.components || []).map(c => ({ component_id: c.id }))
  const body = {
    name: payload.name,
    description: payload.description || '',
    img_url: payload.img_url || '',
    price: Number(payload.price ?? 0).toFixed(2),
    components: componentsPayload,
  }

  router.post('/builds', body, {
    onSuccess: () => {
      modalMessage.value = 'Build sauvegardé avec succès !'
      showModal.value = true
      savingBuild.value = false
    },
    onError: () => {
      modalMessage.value = 'Erreur lors de la sauvegarde.'
      showModal.value = true
      savingBuild.value = false
    }
  })
}

// ---------- Sauvegarder & Commander (API JSON) ----------
const savingAndCheckout = ref(false)

async function saveAndCheckout() {
  if (disableCheckout.value) return
  savingAndCheckout.value = true

  try {
    // Payload JSON générique (API)
    const payload = typeof buildStore.toPayload === 'function'
      ? buildStore.toPayload()
      : {
          name: 'Build personnalisé',
          description: '',
          img_url: null,
          price: Number(totalPrice.value.toFixed(2)),
          components: Object.values(buildStore.build)
            .filter(Boolean)
            .map(c => ({ id: c.id ?? c.component_id, type: 'unknown' })),
        }

    // Pour l’API: on garde components:[{ id, type }], si ton API exige component_id:
    const maybeApiBody = {
      ...payload,
      components: (payload.components || []).map(c => ({ component_id: c.id }))
    }

    // CSRF (au cas où)
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

    // 1) on tente l’API JSON
    const res = await fetch('/api/builds', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {})
      },
      body: JSON.stringify(maybeApiBody)
    })

    const data = await res.json().catch(() => ({}))
    if (!res.ok || !data?.id) {
      // Si l’API n’est pas dispo / ne renvoie pas d’id, on informe :
      throw new Error(data?.message || 'Impossible de sauvegarder le build en API.')
    }

    // 2) Ajouter au panier puis aller au checkout
    cart.add({ type: 'build', id: data.id, qty: 1 })
    router.visit(route('checkout.index'))
  } catch (e) {
    console.error(e)
    modalMessage.value = e?.message || 'Échec de la sauvegarde & commande.'
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

  // 1) Priorité: build complet (le plus fiable)
  try {
    const rawBuild = sessionStorage.getItem('rebuild_build')
    if (rawBuild) {
      const full = JSON.parse(rawBuild)
      if (typeof buildStore.reset === 'function') buildStore.reset()
      if (typeof buildStore.fillFromBuild === 'function') {
        buildStore.fillFromBuild(full)
      }
      sessionStorage.removeItem('rebuild_build')
    } else {
      // 2) Fallback: ancien payload minimal (si présent)
      const raw = sessionStorage.getItem('rebuild_payload')
      if (raw) {
        const items = JSON.parse(raw) || []
        if (typeof buildStore.reset === 'function') buildStore.reset()
        for (const c of items) {
          const key = typeof buildStore.normalizeType === 'function'
            ? buildStore.normalizeType(c.type_key)
            : (c.type_key || '')
          if (!key) continue
          if (typeof buildStore.addComponent === 'function') {
            buildStore.addComponent(key, {
              id: c.id,
              component_id: c.id,
              name: c.name,
              price: c.price,
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

// Déclenche la validation (debounced côté store) à chaque changement du build
watch(
  () => buildStore.build,
  () => {
    buildStore.validateBuild?.()
    setTimeout(() => {
      if (buildStore.errors.length) {
        document
          .getElementById('validation-panel')
          ?.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
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

          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <ComponentCard
              v-for="item in visibleItems"
              :key="item.id"
              :item="item"
              :disableSelect="!buildStore.isCompatible(selectedCategory.key, item.id ?? item.component_id)"
              :show-details="true"
              :show-add-to-cart="false"
              @select="selectComponent"
            />
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
          <div v-if="buildStore.validating" class="text-sm text-gray-600">Validation en cours…</div>

          <div v-if="buildStore.errors.length" class="rounded-lg bg-red-50 border border-red-200 p-3 text-red-800">
            <p class="font-semibold mb-1">Erreurs de compatibilité</p>
            <ul class="list-disc pl-5">
              <li v-for="(e,i) in buildStore.errors" :key="'errm-'+i">{{ e }}</li>
            </ul>
          </div>

          <div v-if="buildStore.warnings.length" class="rounded-lg bg-yellow-50 border border-yellow-200 p-3 text-yellow-800">
            <p class="font-semibold mb-1">Avertissements</p>
            <ul class="list-disc pl-5">
              <li v-for="(w,i) in buildStore.warnings" :key="'warnm-'+i">{{ w }}</li>
            </ul>
          </div>
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
