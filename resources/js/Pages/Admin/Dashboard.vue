<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
defineOptions({ layout: AdminLayout })
const props = defineProps({
  stats: Object
})
</script>

<template>
  <div>
    <h1 class="text-3xl font-bold mb-6">Dashboard Admin</h1>
    <p class="mb-8 text-lg">Bienvenue dans le panneau d'administration, {{ $page.props.auth.user.name }}</p>

    <!-- Widgets de stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white rounded-xl shadow p-6 text-center">
        <div class="text-2xl font-bold">{{ stats.users }}</div>
        <div class="text-gray-600">Utilisateurs</div>
      </div>
      <div class="bg-white rounded-xl shadow p-6 text-center">
        <div class="text-2xl font-bold">{{ stats.builds }}</div>
        <div class="text-gray-600">Builds</div>
      </div>
      <div class="bg-white rounded-xl shadow p-6 text-center">
        <div class="text-2xl font-bold">{{ stats.components }}</div>
        <div class="text-gray-600">Composants</div>
      </div>
    </div>

    <!-- Liens rapides admin -->
    <div class="flex flex-wrap gap-4">
      <Link href="/admin/users" class="bg-darknavy text-white px-5 py-3 rounded-xl shadow hover:bg-primary">Gérer les utilisateurs</Link>
      <Link href="/admin/builds" class="bg-darknavy text-white px-5 py-3 rounded-xl shadow hover:bg-primary">Gérer les builds</Link>
      <Link href="/admin/components" class="bg-darknavy text-white px-5 py-3 rounded-xl shadow hover:bg-primary">Gérer les composants</Link>
      <Link href="/admin/brands" class="bg-darknavy text-white px-5 py-3 rounded-xl shadow hover:bg-primary">Marques</Link>
      <Link href="/admin/categories" class="bg-darknavy text-white px-5 py-3 rounded-xl shadow hover:bg-primary">Catégories</Link>
    </div>
  </div>
</template>

<script setup>

</script>
