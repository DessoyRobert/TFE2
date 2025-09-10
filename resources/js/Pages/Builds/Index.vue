<script setup>
import { router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import FeaturedCarouselSwiper from '@/Components/FeaturedCarouselSwiper.vue'
const props = defineProps({
  // Peut être un tableau (get()) ou un paginator (paginate())
  builds: { type: [Array, Object], default: () => [] },
  featured: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({ sort: 'newest' }) },
})

const page = usePage()
const flashMessage = computed(() => page.props?.flash?.success ?? '')

// Tri depuis le backend (BuildController renvoie props.filters.sort)
const sort = computed({
  get: () => props.filters?.sort ?? 'newest',
  set: (val) => {
    router.get('/builds', { sort: val }, { preserveState: true, replace: true })
  }
})

// Supporte builds = [] OU { data: [...] }
const buildsList = computed(() => {
  if (Array.isArray(props.builds)) return props.builds
  return props.builds?.data ?? []
})

function goToShow(id) {
  router.visit(`/builds/${id}`)
}

function formatEUR(n) {
  const val = Number.isFinite(n) ? n : parseFloat(n ?? 0) || 0
  try {
    return new Intl.NumberFormat('fr-BE', { style: 'currency', currency: 'EUR' }).format(val)
  } catch {
    return `${val.toFixed(2)} €`
  }
}
</script>

<template>
  <div class="max-w-6xl mx-auto py-10 space-y-8">
    <div
      v-if="flashMessage"
      class="mb-4 p-4 bg-green-100 text-green-800 rounded font-medium"
      role="alert"
    >
      {{ flashMessage }}
    </div>

    <div class="flex items-center justify-between gap-3">
      <h1 class="text-2xl font-bold text-darknavy">Nos builds</h1>

      <!-- Sélecteur de tri -->
      <div class="ml-auto flex items-center gap-2">
        <label for="sort" class="text-sm text-darkgray">Trier par</label>
        <select
          id="sort"
          class="border rounded-lg px-3 py-2 text-sm"
          :value="sort"
          @change="e => sort = e.target.value"
        >
          <option value="newest">Plus récents</option>
          <option value="price_asc">Prix ↑</option>
          <option value="price_desc">Prix ↓</option>
        </select>
      </div>
    </div>

    <!-- Carousel Featured -->
    <FeaturedCarouselSwiper
      v-if="featured?.length"
      :items="featured"
      :interval="5000"
      class="mb-6"
    />

    <!-- Grille de builds (style catalogue) -->
    <div v-if="buildsList.length === 0" class="text-darkgray italic p-8 text-center">
      Aucun build trouvé.
    </div>

    <div
      v-else
      class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6"
    >
      <article
        v-for="build in buildsList"
        :key="build.id"
        class="bg-white rounded-2xl shadow border overflow-hidden hover:shadow-md transition"
      >
        <img
          :src="build.img_url || '/images/default.png'"
          class="w-full h-40 object-cover bg-gray-100"
          alt=""
        />
        <div class="p-4 space-y-2">
          <h3 class="font-semibold line-clamp-2">{{ build.name || 'Build personnalisé' }}</h3>
          <div class="text-sm text-gray-600 line-clamp-3 min-h-[3.5rem]">
            {{ build.description || '—' }}
          </div>
          <div class="pt-1 text-lg font-bold">
            {{ formatEUR(
              typeof build.display_total === 'number'
                ? build.display_total
                : (parseFloat(build.display_total ?? NaN) || build.price || 0)
            ) }}
          </div>
          <button
            class="mt-2 w-full py-2 rounded-xl bg-primary hover:bg-cyan text-white text-sm"
            @click="goToShow(build.id)"
          >
            Voir
          </button>
        </div>
      </article>
    </div>

    <!-- Pagination si builds est un paginator -->
    <div
      v-if="!Array.isArray(props.builds) && props.builds?.links"
      class="flex flex-wrap gap-2 justify-center pt-4"
    >
      <button
        v-for="(link, i) in props.builds.links"
        :key="i"
        class="px-3 py-1 rounded border"
        :class="[{ 'bg-darknavy text-white': link.active }]"
        :disabled="!link.url"
        v-html="link.label"
        @click.prevent="link.url && router.visit(link.url, { preserveState: true, preserveScroll: true })"
      />
    </div>
  </div>
</template>

<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
