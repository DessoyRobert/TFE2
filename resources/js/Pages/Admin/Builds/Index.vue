<script setup>
import { router, Link } from '@inertiajs/vue3'
import { reactive, ref, computed } from 'vue'
import GoBackButton from '@/Components/GoBackButton.vue'
import { safeImg, transformCloudinary, FALLBACK_IMG } from '@/utils/imageHelpers'


const props = defineProps({
  builds: { type: Object, required: true },
  filters: {
    type: Object,
    default: () => ({ q: '', status: 'all', featured: 'all', sort: 'created_at', dir: 'desc', per_page: 12 })
  }
})

const q = ref(props.filters?.q ?? '')
const status = ref(props.filters?.status ?? 'all')
const featured = ref(props.filters?.featured ?? 'all')
const sort = ref(props.filters?.sort ?? 'created_at')
const dir = ref(props.filters?.dir ?? 'desc')
const perPage = ref(Number(props.filters?.per_page ?? 12))

const toggling = reactive({})
let searchTimer = null

function buildQuery () {
  return {
    q: q.value || '',
    status: status.value,
    featured: featured.value,
    sort: sort.value,
    dir: dir.value,
    per_page: perPage.value
  }
}
function applyFilters () {
  router.get(route('admin.builds.index'), buildQuery(), { preserveState: true, replace: true })
}
function scheduleSearch () {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => applyFilters(), 400)
}
function resetFilters () {
  q.value = ''
  status.value = 'all'
  featured.value = 'all'
  sort.value = 'created_at'
  dir.value = 'desc'
  perPage.value = 12
  applyFilters()
}
function sortBy (key) {
  if (sort.value === key) {
    dir.value = dir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sort.value = key
    dir.value = 'asc'
  }
  applyFilters()
}
function destroy (id) {
  if (!confirm('Supprimer ce build ?')) return
  router.delete(route('admin.builds.destroy', id), { preserveScroll: true })
}
function goToPage (url) {
  if (!url) return
  router.visit(url, { preserveScroll: true })
}
function toggleVisibility (build) {
  if (toggling[build.id]) return
  const target = !build.is_public
  const previous = build.is_public
  build.is_public = target
  toggling[build.id] = true
  router.patch(
    route('admin.builds.toggle-visibility', build.id),
    { is_public: target },
    {
      preserveScroll: true,
      preserveState: true,
      onError: () => {
        build.is_public = previous
        alert('Impossible de mettre à jour la visibilité.')
      },
      onFinish: () => { toggling[build.id] = false }
    }
  )
}
function formatEUR (value) {
  const n = Number.isFinite(value) ? value : (parseFloat(value ?? NaN) || 0)
  try { return new Intl.NumberFormat('fr-BE', { style: 'currency', currency: 'EUR' }).format(n) }
  catch { return `${n.toFixed(2)} €` }
}
function formatDate (value) {
  if (!value) return '—'
  try { return new Intl.DateTimeFormat('fr-BE', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) }
  catch { return String(value) }
}
const hasData = computed(() => Array.isArray(props.builds?.data) && props.builds.data.length > 0)
function arrow (key) {
  if (sort.value !== key) return '↕'
  return dir.value === 'asc' ? '▲' : '▼'
}
</script>

<template>
  <div class="max-w-7xl mx-auto py-8 space-y-6">
    <GoBackButton class="mb-2" />

    <div class="flex items-center justify-between gap-3">
      <h1 class="text-2xl font-bold text-darknavy">Gestion des Builds</h1>
      <Link :href="route('builds.create')" class="bg-darknavy hover:bg-primary text-white px-4 py-2 rounded-xl text-sm shadow">
        + Nouveau build
      </Link>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow p-4">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div class="md:col-span-2">
          <label class="block text-xs font-medium text-gray-600 mb-1">Recherche</label>
          <input
            v-model="q"
            @input="scheduleSearch"
            type="text"
            class="w-full border rounded-xl px-3 py-2"
            placeholder="Nom, description, #ID…"
            autocomplete="off"
          />
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Statut</label>
          <select v-model="status" @change="applyFilters" class="w-full border rounded-xl px-3 py-2">
            <option value="all">Tous</option>
            <option value="public">Public</option>
            <option value="private">Privé</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">À la une</label>
          <select v-model="featured" @change="applyFilters" class="w-full border rounded-xl px-3 py-2">
            <option value="all">Tous</option>
            <option value="only">Uniquement</option>
            <option value="none">Aucun</option>
          </select>
        </div>

        <div class="flex items-end gap-2">
          <div class="grow">
            <label class="block text-xs font-medium text-gray-600 mb-1">Par page</label>
            <select v-model.number="perPage" @change="applyFilters" class="w-full border rounded-xl px-3 py-2">
              <option :value="12">12</option>
              <option :value="24">24</option>
              <option :value="48">48</option>
            </select>
          </div>
          <button type="button" @click="resetFilters" class="h-10 px-3 rounded-xl border hover:bg-gray-50">Réinitialiser</button>
        </div>
      </div>
    </div>

    <div v-if="hasData" class="bg-white rounded-2xl shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr class="text-left text-gray-700 border-b">
              <th class="py-3 px-4">Aperçu</th>
              <th class="py-3 px-4">
                <button class="inline-flex items-center gap-1 hover:underline" @click="sortBy('id')">
                  ID <span class="text-xs">{{ arrow('id') }}</span>
                </button>
              </th>
              <th class="py-3 px-4">
                <button class="inline-flex items-center gap-1 hover:underline" @click="sortBy('name')">
                  Nom <span class="text-xs">{{ arrow('name') }}</span>
                </button>
              </th>
              <th class="py-3 px-4">
                <button class="inline-flex items-center gap-1 hover:underline" @click="sortBy('display_total')">
                  Prix <span class="text-xs">{{ arrow('display_total') }}</span>
                </button>
              </th>
              <th class="py-3 px-4">
                <button class="inline-flex items-center gap-1 hover:underline" @click="sortBy('is_featured')">
                  À la une <span class="text-xs">{{ arrow('is_featured') }}</span>
                </button>
              </th>
              <th class="py-3 px-4">
                <button class="inline-flex items-center gap-1 hover:underline" @click="sortBy('is_public')">
                  Public <span class="text-xs">{{ arrow('is_public') }}</span>
                </button>
              </th>
              <th class="py-3 px-4">
                <button class="inline-flex items-center gap-1 hover:underline" @click="sortBy('created_at')">
                  Créé le <span class="text-xs">{{ arrow('created_at') }}</span>
                </button>
              </th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="build in builds.data" :key="build.id" class="border-b last:border-0 hover:bg-gray-50">
              <td class="py-3 px-4">
                <img :src="safeImg(build.img_url, 160)" alt="" class="w-16 h-16 object-cover rounded-lg border" />
              </td>

              <td class="py-3 px-4 font-mono text-xs text-gray-700">{{ build.id }}</td>

              <td class="py-3 px-4">
                <div class="font-medium">{{ build.name || '—' }}</div>
                <div class="text-xs text-gray-500 line-clamp-2 max-w-[420px]">{{ build.description || '—' }}</div>
              </td>

              <td class="py-3 px-4 font-semibold">
                {{ formatEUR(
                  typeof build.display_total === 'number'
                    ? build.display_total
                    : (parseFloat(build.display_total ?? NaN) || build.total_price || build.price || 0)
                ) }}
              </td>

              <td class="py-3 px-4">
                <span v-if="build.is_featured" class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-700" title="Affiché dans le carousel">
                  ★ À la une
                  <span v-if="build.featured_rank" class="font-mono">#{{ build.featured_rank }}</span>
                </span>
                <span v-else class="text-xs text-gray-500">—</span>
              </td>

              <td class="py-3 px-4">
                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    :aria-pressed="build.is_public"
                    :disabled="toggling[build.id]"
                    @click="toggleVisibility(build)"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="build.is_public ? 'bg-green-500' : 'bg-gray-300'"
                    title="Basculer visibilité"
                  >
                    <span class="sr-only">Basculer visibilité</span>
                    <span class="inline-block h-4 w-4 transform bg-white rounded-full transition" :class="build.is_public ? 'translate-x-6' : 'translate-x-1'" />
                  </button>
                  <span class="text-xs font-medium" :class="build.is_public ? 'text-green-600' : 'text-gray-500'">
                    {{ build.is_public ? 'Public' : 'Privé' }}
                  </span>
                </div>
              </td>

              <td class="py-3 px-4 text-gray-600">{{ formatDate(build.created_at) }}</td>

              <td class="py-3 px-4 space-x-2 whitespace-nowrap text-right">
                <Link :href="route('admin.builds.edit', build.id)" class="text-blue-600 underline">Éditer</Link>
                <button @click="destroy(build.id)" class="text-red-600 underline">Supprimer</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex gap-2 mt-4 justify-center p-3 border-t bg-gray-50">
        <button
          v-for="link in builds.links"
          :key="link.label"
          :disabled="!link.url || link.active"
          @click="goToPage(link.url)"
          v-html="link.label"
          class="px-3 py-1.5 border rounded-lg disabled:opacity-50 bg-white hover:bg-gray-50"
        />
      </div>
    </div>

    <div v-else class="text-center text-gray-600 py-10">Aucun build trouvé.</div>
  </div>
</template>
