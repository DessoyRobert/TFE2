<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
defineOptions({ layout: AdminLayout })
</script>

<template>
  <div>
    <h1 class="text-3xl font-bold mb-4">Dashboard Admin</h1>
    <p>Bienvenue dans le panneau d’administration, {{ $page.props.auth.user.name }}</p>
  </div>
</template>
