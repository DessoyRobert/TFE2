<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { safeImg, transformCloudinary, FALLBACK_IMG } from '@/utils/imageHelpers'


const props = defineProps({
  // items: [{ id, name, img_url, display_total }]
  items: { type: Array, default: () => [] },
  interval: { type: Number, default: 5000 }, // ms, 0 = pas d'autoplay
  id: { type: String, default: 'featuredCarousel' },
})

const hasSlides = computed(() => Array.isArray(props.items) && props.items.length > 0)
function money(n) {
  const v = Number(n ?? 0)
  try { return new Intl.NumberFormat('fr-BE',{style:'currency',currency:'EUR'}).format(v) }
  catch { return `${v.toFixed(2)} €` }
}
</script>

<template>
  <section v-if="hasSlides" class="relative rounded-2xl overflow-hidden shadow border bg-white">
    <div
      :id="id"
      class="relative"
      data-carousel="slide"
      :data-carousel-interval="interval"
      :data-carousel-autoplay="interval > 0 ? true : false"
    >
      <!-- Slides -->
      <div class="relative h-56 md:h-80 lg:h-96 overflow-hidden">
        <div
          v-for="(it, i) in items"
          :key="it.id"
          class="hidden duration-700 ease-in-out"
          data-carousel-item
        >
          <img
            class="absolute block w-full h-full object-cover"
            :src="safeImg(it.img_url, 960)"
            :alt="`Image du build ${it.name}`"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent flex items-end">
            <div class="p-6 md:p-8 text-white">
              <h3 class="text-2xl md:text-3xl font-extrabold">{{ it.name }}</h3>
              <p class="mt-1 text-lg">À partir de <strong>{{ money(it.display_total) }}</strong></p>
              <div class="mt-3 flex gap-3">
                <Link :href="`/builds/${it.id}`" class="bg-white text-darknavy px-4 py-2 rounded-xl">
                  Voir le build
                </Link>
                <Link :href="`/checkout?build=${it.id}`" class="bg-transparent border border-white px-4 py-2 rounded-xl">
                  Commander
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Indicateurs -->
      <div class="absolute z-30 flex space-x-3 -translate-x-1/2 bottom-4 left-1/2">
        <button
          v-for="(_, i) in items"
          :key="`dot-${i}`"
          type="button"
          class="w-3 h-3 rounded-full"
          aria-label="Aller à la diapositive"
          :data-carousel-slide-to="i"
        />
      </div>

      <!-- Contrôles -->
      <button
        type="button"
        class="absolute top-0 left-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"
        data-carousel-prev
        aria-label="Précédent"
      >
        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/90 group-hover:bg-white shadow">
          ‹
        </span>
      </button>
      <button
        type="button"
        class="absolute top-0 right-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"
        data-carousel-next
        aria-label="Suivant"
      >
        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/90 group-hover:bg-white shadow">
          ›
        </span>
      </button>
    </div>
  </section>
</template>
