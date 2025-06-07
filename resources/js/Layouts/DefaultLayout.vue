
<template>
  <div class="min-h-screen bg-gray-50">
    <HeaderBanner />
    <main class="p-4">
      <slot />
    </main>
  </div>
</template>

<script setup>
import HeaderBanner from '@/Components/HeaderBanner.vue'
</script>
