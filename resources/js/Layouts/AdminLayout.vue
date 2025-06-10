<template>
  <div class="min-h-screen bg-gray-50 text-gray-900">
      <HeaderBanner />
    <main class="max-w-7xl mx-auto p-6">
      <slot />
    </main>
  </div>
</template>

<script setup>
import HeaderBanner from '@/Components/HeaderBanner.vue';

</script>
