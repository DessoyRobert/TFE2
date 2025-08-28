<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useCartStore } from '@/stores/cartStore'

const props = defineProps({
  build: { type: Object, required: true }
})

const cart = useCartStore()

const imageUrl = computed(() => {
  // Le back peut exposer img_url ou imgUrl selon les ressources
  const raw = props.build?.img_url || props.build?.imgUrl || null
  if (!raw) return '/images/placeholder-component.svg'
  // Optimisation Cloudinary éventuelle
  return raw.includes('/upload/')
    ? raw.replace('/upload/', '/upload/f_auto,q_auto,w_640/')
    : raw
})

const priceText = computed(() => {
  const val = props.build?.price
  if (val === null || val === undefined || val === '') return '—'
  const n = Number(val)
  return Number.isFinite(n) ? `${n.toFixed(2)} €` : '—'
})

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

function addAndCheckout() {
  addToCart()
  const href = typeof route !== 'undefined' ? route('checkout.index') : '/checkout'
  router.visit(href)
}

/** → Patch Recréer : hydrate Create.vue via sessionStorage */
function recreate() {
  try {
    sessionStorage.setItem('rebuild_build', JSON.stringify(props.build))
  } catch (e) {
    // quota full / privacy mode : on ignore
  }
  router.visit('/builds/create')
}
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-10">
    <div class="bg-white rounded-xl shadow-md border p-8 space-y-6">
      <!-- Titre -->
      <div>
        <h1 class="text-3xl font-bold text-darknavy">
          {{ build.name }}
        </h1>
        <p v-if="build.description" class="text-gray-600 mt-2">
          {{ build.description }}
        </p>
      </div>

      <!-- Image + actions -->
      <div class="flex items-center gap-6">
        <img
          :src="imageUrl"
          alt="Image du build"
          class="max-w-xs w-full rounded-xl shadow object-contain bg-gray-50"
          width="640" height="360"
          loading="lazy" decoding="async"
        />

        <!-- Actions -->
        <div class="flex-1 space-y-3">
          <div class="text-xl font-bold">
            Prix total :
            <span class="text-primary">{{ priceText }}</span>
          </div>

          <div class="flex flex-wrap gap-2">
            <button
              type="button"
              @click="addToCart"
              class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white"
            >
              Ajouter au panier
            </button>

            <button
              type="button"
              @click="addAndCheckout"
              class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white"
            >
              Commander ce build
            </button>

            <!-- Nouveau : Recréer -->
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
      </div>

      <!-- Liste des composants -->
      <div>
        <h2 class="text-xl font-semibold mb-3">Composants</h2>
        <ul class="space-y-1">
          <li
            v-for="component in build.components || []"
            :key="component.id"
            class="flex gap-2 items-center hover:text-primary transition cursor-pointer"
            @click="showDetailPage(component)"
          >
            <span class="font-medium" v-if="component.brand?.name || component.brand">
              {{ component.brand?.name ?? component.brand }}
            </span>
            <span>{{ component.name }}</span>
          </li>
        </ul>

        <p v-if="!build.components || build.components.length === 0" class="text-gray-500">
          Aucun composant listé pour ce build.
        </p>
      </div>
    </div>
  </div>
</template>
