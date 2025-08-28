<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  item: { type: Object, required: true },
  /** Grise le bouton "Sélectionner" si non compatible */
  disableSelect: { type: Boolean, default: false },
  /** Afficher un lien "Détails" vers la page composant */
  showDetails: { type: Boolean, default: true },
  /** Afficher un bouton "Ajouter au panier" (utile hors builder) */
  showAddToCart: { type: Boolean, default: false },
  /** ISO currency code */
  currency: { type: String, default: 'EUR' },
})

const emit = defineEmits(['select', 'add', 'details'])

const fmt = computed(
  () => new Intl.NumberFormat('fr-BE', { style: 'currency', currency: props.currency })
)

const priceText = computed(() => {
  const n = Number(props.item?.price ?? 0)
  return fmt.value.format(Number.isFinite(n) ? n : 0)
})

function pickImageUrl(item) {
  const url =
    item?.images?.[0]?.url ??
    item?.component?.images?.[0]?.url ??
    item?.component?.img_url ??
    item?.img_url ??
    null

  if (!url) return '/images/placeholder-component.svg'
  return url.includes('/upload/')
    ? url.replace('/upload/', '/upload/f_auto,q_auto,w_480/')
    : url
}

const detailsHref = computed(() => {
  const id = props.item?.id ?? props.item?.component_id
  if (!id) return null
  try {
    // Ziggy route() si dispo
    // eslint-disable-next-line no-undef
    return typeof route !== 'undefined' ? route('components.details', id) : `/components/${id}/details`
  } catch {
    return `/components/${id}/details`
  }
})
</script>

<template>
  <div class="bg-white rounded-xl shadow-md p-4 flex flex-col justify-between">
    <div class="block group">
      <img
        :src="pickImageUrl(item)"
        :alt="item?.name || 'Composant'"
        width="480" height="240"
        class="w-full h-32 object-contain mb-2 transition-transform group-hover:scale-[1.02]"
        loading="lazy" decoding="async"
      />

      <h3 class="font-semibold group-hover:text-primary transition-colors">
        {{ item?.name }}
      </h3>

      <p class="text-gray-600">
        {{ priceText }}
      </p>

      <p v-if="item?.brand?.name || item?.brand" class="text-xs text-gray-500 mt-1">
        {{ item?.brand?.name ?? item?.brand }}
      </p>
    </div>

    <div class="mt-3 flex items-center gap-2">
      <button
        type="button"
        class="flex-1 bg-primary hover:bg-cyan text-white px-3 py-2 rounded-lg disabled:opacity-50"
        :disabled="disableSelect"
        @click="emit('select', item)"
      >
        Sélectionner
      </button>

      <Link
        v-if="showDetails && detailsHref"
        :href="detailsHref"
        class="px-3 py-2 text-sm rounded-lg border border-gray-200 hover:bg-gray-50"
        @click="emit('details', item)"
      >
        Détails
      </Link>

      <button
        v-if="showAddToCart"
        type="button"
        class="px-3 py-2 text-sm rounded-lg bg-blue-600 hover:bg-blue-700 text-white"
        @click="emit('add', item)"
      >
        + Panier
      </button>
    </div>
  </div>
</template>
