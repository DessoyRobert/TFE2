<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Navigation, Pagination, Autoplay, A11y } from 'swiper/modules'

const props = defineProps({
  // [{ id, name, img_url, display_total }]
  items: { type: Array, default: () => [] },
  interval: { type: Number, default: 5000 }, // 0 = pas d’autoplay
  loop: { type: Boolean, default: true },
})

const hasSlides = computed(() => Array.isArray(props.items) && props.items.length > 0)
const modules = [Navigation, Pagination, Autoplay, A11y]

function money(n) {
  const v = Number(n ?? 0)
  try { return new Intl.NumberFormat('fr-BE', { style:'currency', currency:'EUR' }).format(v) }
  catch { return `${v.toFixed(2)} €` }
}
</script>

<template>
  <section v-if="hasSlides" class="relative rounded-2xl overflow-hidden shadow border bg-white">
    <Swiper
      :modules="modules"
      :loop="loop"
      :autoplay="interval > 0 ? { delay: interval, pauseOnMouseEnter: true, disableOnInteraction: false } : false"
      :pagination="{ clickable: true }"
      :navigation="true"
      :a11y="{ enabled: true }"
      class="w-full"
    >
      <SwiperSlide v-for="it in items" :key="it.id">
        <div class="grid md:grid-cols-2">
          <!-- Image -->
          <div class="relative aspect-[16/9] md:aspect-auto">
            <img
              class="w-full h-full object-cover"
              :src="it.img_url || '/images/default.png'"
              :alt="`Image du build ${it.name}`"
              loading="lazy"
              decoding="async"
              sizes="(min-width: 768px) 50vw, 100vw"
            />
            <div class="absolute inset-0 md:hidden bg-gradient-to-t from-black/50 to-transparent" />
          </div>

          <!-- Texte -->
          <div class="p-6 md:p-8 flex flex-col justify-center gap-4">
            <p class="text-xs tracking-wide uppercase text-gray-500">Mise en avant</p>
            <h3 class="text-2xl md:text-3xl font-extrabold text-darknavy leading-tight">
              {{ it.name }}
            </h3>
            <div class="text-lg md:text-xl text-gray-800">
              À partir de <strong>{{ money(it.display_total) }}</strong>
            </div>
            <div class="flex items-center gap-3 pt-2">
              <Link :href="`/builds/${it.id}`" class="bg-primary hover:bg-cyan text-white px-5 py-2 rounded-xl shadow">
                Voir le build
              </Link>
              <Link :href="`/checkout?build=${it.id}`" class="border px-5 py-2 rounded-xl hover:bg-gray-50">
                Commander
              </Link>
            </div>
          </div>
        </div>
      </SwiperSlide>
    </Swiper>
  </section>
</template>
