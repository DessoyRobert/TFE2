<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useCartStore } from '@/stores/cartStore'

const props = defineProps({
  build: { type: Object, required: true }
})

const cart = useCartStore()

/* ----------------------- Utils ----------------------- */
function formatEUR (n) {
  const num = Number(n ?? 0)
  try {
    return new Intl.NumberFormat('fr-BE', { style: 'currency', currency: 'EUR' }).format(num)
  } catch {
    return `${num.toFixed(2)} €`
  }
}

/* ------------------- Image principale ------------------- */
const imageUrl = computed(() => {
  const raw = props.build?.img_url || props.build?.imgUrl || null
  if (!raw) return '/images/placeholder-component.svg'
  // Optimisation Cloudinary si l’URL contient /upload/
  return raw.includes('/upload/')
    ? raw.replace('/upload/', '/upload/f_auto,q_auto,w_960/')
    : raw
})

/* ------------------- Prix (toujours live) ------------------- */
/**
 * Priorité:
 * 1) build.display_total (fourni par le backend = somme live SQL)
 * 2) fallback front: somme des composants (price * qty(=1 si pivot absent))
 */
const liveTotal = computed(() => {
  const dt = props.build?.display_total
  const parsed = Number(dt)
  if (Number.isFinite(parsed) && parsed >= 0) return parsed

  // Fallback: somme côté front (quantité=1 si pivot absent)
  const items = Array.isArray(props.build?.components) ? props.build.components : []
  return items.reduce((sum, c) => {
    const qty = Number(c?.pivot?.quantity ?? 1) || 1
    const price = Number(c?.price ?? 0) || 0
    return sum + qty * price
  }, 0)
})

/* ------------------- Navigation / actions ------------------- */
function showDetailPage(component) {
  if (!component || !component.id) return
  const href = typeof route !== 'undefined'
    ? route('components.details', component.id)
    : `/components/${component.id}/details`
  router.visit(href, { preserveState: true, preserveScroll: true })
}

function addToCart() {
  if (!props.build?.id) return
  cart.add({ type: 'build', id: props.build.id, qty: 1 })
}

async function addAndCheckout() {
  if (!props.build?.id) return
  cart.add({ type: 'build', id: props.build.id, qty: 1 })
  if (typeof cart.flush === 'function') await cart.flush()
  const href = typeof route !== 'undefined' ? route('checkout.index') : '/checkout'
  router.visit(href)
}

/** Préremplit l’éditeur (/builds/create) avec ce build */
function recreate() {
  try {
    sessionStorage.setItem('rebuild_build', JSON.stringify(props.build))
  } catch { /* quota / privacy : ignore */ }
  router.visit('/builds/create')
}
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-10">
    <div class="bg-white rounded-2xl shadow-md border p-6 md:p-8 space-y-6">
      <!-- En-tête -->
      <header>
        <h1 class="text-2xl md:text-3xl font-bold text-darknavy">
          {{ build.name || 'Build' }}
        </h1>
        <p v-if="build.description" class="text-gray-600 mt-2">
          {{ build.description }}
        </p>
      </header>

      <!-- Media + actions -->
      <section class="grid md:grid-cols-2 gap-6 items-start">
        <figure class="w-full">
          <img
            :src="imageUrl"
            alt="Image du build"
            class="w-full max-h-[360px] object-contain rounded-xl shadow bg-gray-50"
            width="960" height="540"
            loading="lazy" decoding="async"
          />
        </figure>

        <div class="space-y-4">
          <div class="text-lg md:text-xl font-semibold">
            Prix total :
            <span class="text-primary font-bold">{{ formatEUR(liveTotal) }}</span>
          </div>

          <div class="flex flex-wrap gap-2">
            <button
              type="button"
              @click="addToCart"
              class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white"
              aria-label="Ajouter ce build au panier"
            >
              Ajouter au panier
            </button>

            <button
              type="button"
              @click="addAndCheckout"
              class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white"
              aria-label="Commander ce build"
            >
              Commander ce build
            </button>

            <button
              type="button"
              @click="recreate"
              class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white"
              title="Ouvrir l’éditeur avec ces composants pré-remplis"
            >
              Recréer ce build
            </button>

            <Link
              :href="typeof route !== 'undefined' ? route('builds.index') : '/builds'"
              class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800"
            >
              Retour aux builds
            </Link>
          </div>
        </div>
      </section>

      <!-- Liste composants -->
      <section>
        <h2 class="text-xl font-semibold mb-3">Composants</h2>

        <ul v-if="build.components?.length" class="divide-y">
          <li
            v-for="component in build.components"
            :key="component.id"
            class="py-2 flex items-center gap-3 cursor-pointer group"
            @click="showDetailPage(component)"
            :aria-label="`Voir le composant ${component.name}`"
          >
            <span
              class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700"
              v-if="component.brand?.name || component.brand"
            >
              {{ component.brand?.name ?? component.brand }}
            </span>

            <span class="truncate group-hover:text-primary">
              {{ component.name }}
            </span>

            <span class="ml-auto text-sm text-gray-600">
              {{ formatEUR(component.price ?? 0) }}
            </span>
          </li>
        </ul>

        <p v-else class="text-gray-500">
          Aucun composant listé pour ce build.
        </p>
      </section>
    </div>
  </div>
</template>
