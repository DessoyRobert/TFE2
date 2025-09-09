<script setup>
import { router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  builds: { type: [Array, Object], default: () => [] }, // paginator ou array
  isAdmin: { type: Boolean, default: false },
})

const page = usePage()
const flashMessage = computed(() => page.props?.flash?.success ?? '')

// tri (server-side)
const sort = computed({
  get: () => page.props?.filters?.sort ?? 'newest',
  set: (val) => router.get('/builds', { sort: val }, { preserveState: true, replace: true })
})

// liste (array ou paginator)
const buildsList = computed(() => Array.isArray(props.builds) ? props.builds : (props.builds?.data ?? []))

// -------- Drawer filtres (client-side pour l’instant) --------
const showFilters = ref(false)
const q = ref('')                        // recherche plein texte
const priceMin = ref('')
const priceMax = ref('')
const cpuVendors = ref([])               // ['intel','amd']
const gpuVendors = ref([])               // ['nvidia','amd']
const onlyPublic = ref(false)

// Helpers
function primaryImage(b) {
  return b.img_url
    || b.components?.[0]?.images?.[0]?.url
    || '/images/default.png'
}
function euro(v) {
  const n = Number.isFinite(v) ? v : parseFloat(v ?? 0) || 0
  try { return new Intl.NumberFormat('fr-BE', { style:'currency', currency:'EUR' }).format(n) }
  catch { return `${n.toFixed(2)} €` }
}
function specOf(b, typeSlug) {
  // suppose que chaque component a type.name présent; slug approximatif
  const it = (b.components || []).find(c => (c.component_type?.slug || c.component_type?.name || '').toLowerCase().includes(typeSlug))
  return it?.name || null
}
function hasVendor(b, typeSlug, vendor) {
  const it = (b.components || []).find(c => (c.component_type?.slug || c.component_type?.name || '').toLowerCase().includes(typeSlug))
  return it ? new RegExp(vendor,'i').test(it.brand?.name || it.name || '') : false
}

// Filtrage client-side (facile à brancher en backend plus tard)
const filtered = computed(() => {
  return buildsList.value.filter(b => {
    const price = typeof b.display_total === 'number'
      ? b.display_total
      : (parseFloat(b.display_total ?? NaN) || b.price || 0)

    if (priceMin.value && price < Number(priceMin.value)) return false
    if (priceMax.value && price > Number(priceMax.value)) return false

    if (onlyPublic.value && b.is_public === false) return false

    // CPU/GPU vendors
    if (cpuVendors.value.length) {
      const ok = cpuVendors.value.some(v => hasVendor(b, 'cpu', v))
      if (!ok) return false
    }
    if (gpuVendors.value.length) {
      const ok = gpuVendors.value.some(v => hasVendor(b, 'gpu', v))
      if (!ok) return false
    }

    // recherche plein texte (nom + composants)
    if (q.value) {
      const hay = [
        b.name,
        ...(b.components || []).map(c => [c.name, c.brand?.name, c.component_type?.name].filter(Boolean).join(' '))
      ].join(' ').toLowerCase()
      if (!hay.includes(q.value.toLowerCase())) return false
    }
    return true
  })
})

// Actions
function goToShow(id){ router.visit(`/builds/${id}`) }
function goToEdit(id){ router.visit(props.isAdmin ? `/admin/builds/${id}/edit` : `/builds/${id}/edit`) }
function destroyBuild(id){ if (props.isAdmin && confirm('Supprimer ce build ?')) router.delete(`/admin/builds/${id}`) }
function recreateBuild(build){
  try { sessionStorage.setItem('rebuild_build', JSON.stringify(build)) } catch {}
  router.visit('/builds/create', { preserveScroll: true })
}

// pagination
const hasPagination = computed(() => !Array.isArray(props.builds) && !!props.builds?.links)
function visitLink(link){ if (link?.url) router.visit(link.url) }
</script>

<template>
  <div class="max-w-7xl mx-auto py-6 space-y-6">
    <div v-if="flashMessage" class="p-4 bg-green-100 text-green-800 rounded font-medium" role="alert">
      {{ flashMessage }}
    </div>

    <!-- Header + Controls -->
    <div class="flex flex-wrap items-center gap-3">
      <h1 class="text-2xl font-bold text-darknavy">RDY-like — Builds prêts à l’emploi</h1>

      <div class="ml-auto flex flex-wrap items-center gap-2">
        <!-- Search -->
        <input
          type="search"
          v-model="q"
          placeholder="Rechercher un build ou composant…"
          class="border rounded-xl px-3 py-2 text-sm w-64"
        />

        <!-- Sort -->
        <select class="border rounded-xl px-3 py-2 text-sm" :value="sort" @change="e => sort = e.target.value">
          <option value="newest">Plus récents</option>
          <option value="price_desc">Prix : élevé → bas</option>
          <option value="price_asc">Prix : bas → élevé</option>
        </select>

        <button class="px-3 py-2 rounded-xl border text-sm" @click="showFilters = true">
          Sort & Filter
        </button>

        <button
          @click="$inertia.visit('/builds/create')"
          class="bg-primary hover:bg-cyan text-white px-4 py-2 rounded-xl text-sm shadow"
        >
          + Créer un build
        </button>
      </div>
    </div>

    <!-- Results count -->
    <div class="text-sm text-darkgray">
      {{ filtered.length }} résultat(s)
    </div>

    <!-- Grid of cards -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <article
        v-for="b in filtered"
        :key="b.id"
        class="bg-white rounded-2xl shadow-sm border overflow-hidden flex flex-col"
      >
        <div class="relative">
          <img :src="primaryImage(b)" alt="" class="w-full h-44 object-cover">
          <div v-if="b.is_public" class="absolute top-3 left-3 px-2 py-1 text-xs bg-emerald-600 text-white rounded">
            Public
          </div>
        </div>

        <div class="p-4 flex flex-col gap-3 flex-1">
          <h3 class="font-semibold line-clamp-2">{{ b.name || 'Build personnalisé' }}</h3>

          <!-- Specs style iBUYPOWER: CPU / GPU / RAM / Storage -->
          <ul class="text-sm text-gray-700 space-y-1">
            <li v-if="specOf(b,'cpu')"><strong>CPU:</strong> {{ specOf(b,'cpu') }}</li>
            <li v-if="specOf(b,'gpu')"><strong>GPU:</strong> {{ specOf(b,'gpu') }}</li>
            <li v-if="specOf(b,'ram')"><strong>Mémoire:</strong> {{ specOf(b,'ram') }}</li>
            <li v-if="specOf(b,'storage')"><strong>Stockage:</strong> {{ specOf(b,'storage') }}</li>
          </ul>

          <div class="mt-auto">
            <div class="text-2xl font-bold">{{ euro(typeof b.display_total==='number' ? b.display_total : (parseFloat(b.display_total ?? NaN) || b.price || 0)) }}</div>
            <p class="text-xs text-gray-500">Livraison rapide — satisfait ou remboursé</p>
          </div>
        </div>

        <div class="p-4 border-t flex items-center gap-2">
          <button class="px-3 py-2 rounded-xl bg-darknavy text-white hover:bg-violetdark text-sm" @click="goToShow(b.id)">
            Voir les détails
          </button>

          <button class="px-3 py-2 rounded-xl border text-sm" @click="recreateBuild(b)">
            Dupliquer
          </button>

          <div class="ml-auto flex items-center gap-2" v-if="isAdmin">
            <button class="px-3 py-2 rounded-xl border text-sm" @click="goToEdit(b.id)">Éditer</button>
            <button class="px-3 py-2 rounded-xl bg-red-600 text-white text-sm" @click="destroyBuild(b.id)">Supprimer</button>
          </div>
        </div>
      </article>
    </div>

    <!-- Pagination (si server-side) -->
    <div v-if="hasPagination" class="flex flex-wrap gap-2 justify-center pt-4">
      <button
        v-for="(link, i) in props.builds.links"
        :key="i"
        class="px-3 py-1 rounded border text-sm"
        :class="[{ 'bg-darknavy text-white': link.active }]"
        :disabled="!link.url"
        v-html="link.label"
        @click.prevent="visitLink(link)"
      />
    </div>

    <!-- Drawer Filters -->
    <div v-if="showFilters" class="fixed inset-0 z-40">
      <div class="absolute inset-0 bg-black/40" @click="showFilters = false"></div>
      <aside class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-xl p-5 overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold">Sort & Filter</h2>
          <button class="text-sm underline" @click="() => { q=''; priceMin=''; priceMax=''; cpuVendors=[]; gpuVendors=[]; onlyPublic=false }">
            Réinitialiser
          </button>
        </div>

        <div class="space-y-6">
          <div>
            <label class="block text-sm font-medium mb-1">Recherche</label>
            <input v-model="q" type="search" class="border rounded-xl px-3 py-2 w-full" placeholder="Nom, CPU, GPU…" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium mb-1">Prix min (€)</label>
              <input v-model="priceMin" type="number" min="0" class="border rounded-xl px-3 py-2 w-full" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Prix max (€)</label>
              <input v-model="priceMax" type="number" min="0" class="border rounded-xl px-3 py-2 w-full" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">CPU</label>
            <div class="flex flex-wrap gap-2">
              <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" value="intel" v-model="cpuVendors" /> Intel
              </label>
              <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" value="amd" v-model="cpuVendors" /> AMD
              </label>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">GPU</label>
            <div class="flex flex-wrap gap-2">
              <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" value="nvidia" v-model="gpuVendors" /> NVIDIA
              </label>
              <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" value="amd" v-model="gpuVendors" /> AMD
              </label>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <input id="onlyPublic" type="checkbox" v-model="onlyPublic" />
            <label for="onlyPublic" class="text-sm">Afficher uniquement les builds publics</label>
          </div>

          <div class="pt-2">
            <button class="w-full px-4 py-2 rounded-xl bg-darknavy text-white" @click="showFilters = false">Afficher les résultats</button>
          </div>
        </div>
      </aside>
    </div>
  </div>
</template>
