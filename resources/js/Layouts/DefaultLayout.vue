<script setup>
import HeaderBanner from '@/Components/HeaderBanner.vue'
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <HeaderBanner />
    <main class="max-w-8xl mx-auto">
      <slot />
    </main>
  </div>
</template>
