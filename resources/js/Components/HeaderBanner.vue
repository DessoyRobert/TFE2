<template>
  <header class="flex items-center justify-between px-6 py-4 bg-darknavy shadow-md rounded-b-2xl">
    <!-- Titre/logo, retourne à l'accueil -->
    <router-link to="/" class="text-2xl font-bold text-primary hover:text-cyan transition-all select-none">
      JarvisTech <span class="text-violetdark">/ PCBuilder</span>
    </router-link>
    <!-- Slot customisable à droite -->
    <div class="flex items-center gap-4">
      <slot>
        <Link href="/login" class="bg-primary hover:bg-cyan text-white px-4 py-2 rounded-xl shadow">
          Se connecter
        </Link>
      </slot>
    </div>
  </header>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
