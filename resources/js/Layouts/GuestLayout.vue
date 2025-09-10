<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'

// Fallback si le logo Cloudinary ne charge pas
const useFallbackLogo = ref(false)
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
      <!-- En-tête brand -->
      <div class="flex items-center justify-center mb-6">
        <Link href="/" class="inline-flex items-center gap-3 hover:opacity-90 transition">
          <img
            v-if="!useFallbackLogo"
            src="https://res.cloudinary.com/djllwl8c0/image/upload/f_auto,q_auto,w_192,h_48,c_fit/Logo-JarvisTech-PNG-normalsansfond_pgxlrj.png"
            alt="JarvisTech"
            class="h-10 w-auto"
            @error="useFallbackLogo = true"
            decoding="async"
            fetchpriority="high"
          />
          <ApplicationLogo v-else class="h-10 w-10 text-[#1ec3a6]" />
          <span class="text-xl font-bold text-[#1ec3a6]">PCBuilder</span>
        </Link>
      </div>

      <!-- Carte -->
      <div class="bg-white rounded-2xl shadow-2xl px-6 py-8 sm:px-8">
        <slot />
      </div>
    </div>
  </div>
</template>
