<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex flex-col justify-center items-center pt-6 sm:pt-0">
    <div>
      <Link href="/">
        <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
      </Link>
    </div>

    <div class="mt-6 w-full bg-white shadow-md rounded-lg px-6 py-4 sm:max-w-md">
      <slot />
    </div>
  </div>
</template>
