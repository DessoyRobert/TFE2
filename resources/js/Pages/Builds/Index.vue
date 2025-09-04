<script setup>
import { router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  // Peut être un tableau (get()) ou un paginator (paginate())
  builds: { type: [Array, Object], default: () => [] },
  isAdmin: { type: Boolean, default: false },
})

const page = usePage()
const flashMessage = computed(() => page.props?.flash?.success ?? '')

// Supporte builds = [] OU { data: [...] }
const buildsList = computed(() => {
  if (Array.isArray(props.builds)) return props.builds
  return props.builds?.data ?? []
})

function goToShow(id) {
  router.visit(`/builds/${id}`)
}

function goToEdit(id) {
  router.visit(`/builds/${id}/edit`)
}

function destroyBuild(id) {
  if (confirm('Supprimer ce build ?')) {
    router.delete(`/builds/${id}`)
  }
}

// Recréer un build : stash dans sessionStorage puis redirige vers /builds/create
function recreateBuild(build) {
  try {
    sessionStorage.setItem('rebuild_build', JSON.stringify(build))
  } catch (e) {
    console.warn('SessionStorage indisponible pour rebuild_build', e)
  }
  router.visit('/builds/create', { preserveScroll: true })
}

function formatEUR(n) {
  const val = Number.isFinite(n) ? n : parseFloat(n ?? 0) || 0
  return new Intl.NumberFormat('fr-BE', { style: 'currency', currency: 'EUR' }).format(val)
}
</script>

<template>
  <div class="max-w-5xl mx-auto py-10 space-y-8">
    <div
      v-if="flashMessage"
      class="mb-4 p-4 bg-green-100 text-green-800 rounded font-medium"
      role="alert"
    >
      {{ flashMessage }}
    </div>

    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-darknavy mb-6">Liste des builds</h1>
      <button
        @click="$inertia.visit('/builds/create')"
        class="bg-primary hover:bg-cyan text-white px-4 py-2 rounded-xl text-sm shadow"
      >
        + Créer un build
      </button>
    </div>

    <div v-if="buildsList.length === 0" class="text-darkgray italic p-8 text-center">
      Aucun build trouvé.
    </div>

    <table
      v-else
      class="w-full text-sm border rounded-xl bg-white shadow overflow-hidden"
    >
      <thead class="bg-lightgray text-darknavy font-semibold">
        <tr>
          <th class="px-4 py-2 text-left">Nom</th>
          <th class="px-4 py-2 text-left">Prix</th>
          <th class="px-4 py-2 text-left">Composants</th>
          <th class="px-4 py-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="build in buildsList"
          :key="build.id"
          class="border-b last:border-0 hover:bg-lightgray/50 transition"
        >
          <td class="px-4 py-3 font-medium">
            {{ build.name || 'Build personnalisé' }}
          </td>

          <!-- Prix fiable : display_total (accessor modèle) -> fallback sur price -->
          <td class="px-4 py-3">
            {{ formatEUR(
              typeof build.display_total === 'number'
                ? build.display_total
                : build.price
            ) }}
          </td>

          <td class="px-4 py-3">
            <ul class="list-disc ml-5 space-y-1">
              <li
                v-for="component in (build.components || [])"
                :key="component.id"
                class="text-darknavy"
              >
                {{ component.name }}
                <span class="text-xs text-darkgray">
                  ({{ (component.brand && component.brand.name) ? component.brand.name : '—' }})
                </span>
              </li>
            </ul>
          </td>

          <td class="px-4 py-3 space-y-2 space-x-2 flex flex-wrap">
            <button
              class="bg-primary hover:bg-cyan text-white px-3 py-1 rounded-xl text-xs"
              @click="goToShow(build.id)"
            >
              Voir
            </button>

            <button
              class="bg-[#1ec3a6] hover:bg-[#23b59b] text-white px-3 py-1 rounded-xl text-xs"
              @click="recreateBuild(build)"
            >
              Recréer
            </button>

            <button
              v-if="isAdmin"
              class="bg-darknavy hover:bg-violetdark text-white px-3 py-1 rounded-xl text-xs"
              @click="goToEdit(build.id)"
            >
              Éditer
            </button>

            <button
              v-if="isAdmin"
              class="bg-red-500 hover:bg-red-700 text-white px-3 py-1 rounded-xl text-xs"
              @click="destroyBuild(build.id)"
            >
              Supprimer
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- (Optionnel) Pagination si builds est un paginator -->
    <div
      v-if="!Array.isArray(props.builds) && props.builds?.links"
      class="flex flex-wrap gap-2 justify-end pt-4"
    >
      <button
        v-for="(link, i) in props.builds.links"
        :key="i"
        class="px-3 py-1 rounded border"
        :class="[{ 'bg-darknavy text-white': link.active }]"
        :disabled="!link.url"
        v-html="link.label"
        @click.prevent="link.url && router.visit(link.url)"
      />
    </div>
  </div>
</template>
