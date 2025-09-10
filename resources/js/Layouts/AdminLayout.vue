<script setup>
import HeaderBanner from '@/Components/HeaderBanner.vue';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();

// Méthode 1 : Redirection directe
if (page.props.auth?.user && !page.props.auth.user.is_admin) {
  // Redirige vers l'accueil ou une page d'erreur 
  router.visit('/builds/create', { replace: true });
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 text-gray-900">
    <HeaderBanner />
    <main class="max-w-7xl mx-auto p-6">
      <slot />
    </main>
  </div>
</template>
