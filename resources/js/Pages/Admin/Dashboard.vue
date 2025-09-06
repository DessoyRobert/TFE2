<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })
const props = defineProps({ stats: Object })
</script>

<template>
  <div>
    <h1 class="text-3xl font-bold mb-6">Dashboard Admin</h1>
    <p class="mb-8 text-lg">
      Bienvenue dans le panneau d'administration, 
      <span class="font-semibold text-darknavy">{{ $page.props.auth.user.name }}</span>
    </p>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white rounded-xl shadow p-6 text-center">
        <div class="text-2xl font-bold">{{ props.stats.users }}</div>
        <div class="text-gray-600">Utilisateurs</div>
      </div>
      <div class="bg-white rounded-xl shadow p-6 text-center">
        <div class="text-2xl font-bold">{{ props.stats.builds }}</div>
        <div class="text-gray-600">Builds</div>
      </div>
      <div class="bg-white rounded-xl shadow p-6 text-center">
        <div class="text-2xl font-bold">{{ props.stats.components }}</div>
        <div class="text-gray-600">Composants</div>
      </div>
      <div class="bg-white rounded-xl shadow p-6 text-center">
        <div class="text-2xl font-bold">{{ props.stats.brands }}</div>
        <div class="text-gray-600">Marques</div>
      </div>
      <div class="bg-white rounded-xl shadow p-6 text-center">
        <div class="text-2xl font-bold">{{ props.stats.categories }}</div>
        <div class="text-gray-600">Catégories</div>
      </div>
    </div>

    <!-- Liens rapides -->
    <div class="flex flex-wrap gap-4">
      <Link href="/admin/users" class="bg-darknavy text-white px-5 py-3 rounded-xl shadow hover:bg-primary transition">utilisateurs</Link>
      <Link href="/admin/builds" class="bg-darknavy text-white px-5 py-3 rounded-xl shadow hover:bg-primary transition">builds</Link>
      <Link href="/admin/components" class="bg-darknavy text-white px-5 py-3 rounded-xl shadow hover:bg-primary transition">composants</Link>
      <Link href="/admin/brands" class="bg-darknavy text-white px-5 py-3 rounded-xl shadow hover:bg-primary transition">Marques</Link>
      <Link href="/admin/categories" class="bg-darknavy text-white px-5 py-3 rounded-xl shadow hover:bg-primary transition">Catégories</Link>
      <Link href="/admin/component-types" class="bg-darknavy text-white px-5 py-3 rounded-xl shadow hover:bg-primary transition">Types de composants</Link>
      <Link href="/admin/compatibility-rules" class="bg-darknavy text-white px-5 py-3 rounded-xl shadow hover:bg-primary transition">Règles de compatibilité</Link>
      <Link href="/admin/orders" class="bg-darknavy text-white px-5 py-3 rounded-xl shadow hover:bg-primary transition">Commandes</Link>
    </div>
  </div>
</template>

