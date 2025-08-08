<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth.user)
const isAdmin = computed(() => user.value?.is_admin)

function logout() {
  router.post(route('logout'))
}
</script>

<template>
  <header class="flex items-center justify-between px-6 py-4 bg-darknavy shadow-md rounded-b-2xl">
    <div class="flex items-center gap-3">
      <!-- Logo JarvisTech optimisé Cloudinary -->
      <img
        src="https://res.cloudinary.com/djllwl8c0/image/upload/f_auto,q_auto,w_192,h_48,c_fit/Logo-JarvisTech-PNG-normalsansfond_pgxlrj.png"
        alt="Logo JarvisTech"
        width="192" height="48"
        decoding="async"
        fetchpriority="high"
        class="h-12 w-auto block"
      />

      <!-- Lien vers l’accueil -->
      <Link href="/" class="text-2xl font-bold text-primary hover:text-cyan transition-all select-none">
        JarvisTech <span class="text-violetdark">/ PCBuilder</span>
      </Link>
    </div>

    <div class="flex items-center gap-4">
      <Link href="/builds/create" class="text-white hover:text-cyan">Créer un Build</Link>
      <Link href="/builds" class="text-white hover:text-cyan">Tous les Builds</Link>
      <Link href="/components" class="text-white hover:text-cyan transition-all">Tous les composants</Link>
      <a href="https://jarvistech.be/#contact" target="_blank" rel="noopener noreferrer" class="text-white hover:text-cyan">
        Contact
      </a>

      <template v-if="user">
        <template v-if="isAdmin">
          <Link href="/admin/dashboard" class="text-white hover:text-cyan">Dashboard Admin</Link>
          <Link href="/admin/compatibility-rules" class="text-white hover:text-cyan">Compatibilités</Link>
        </template>

        <Link href="/dashboard" class="text-white hover:text-cyan">Dashboard</Link>
        <Link href="/profile" class="text-white hover:text-cyan">{{ user.name }}</Link>

        <button
          @click="logout"
          class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl shadow"
        >
          Se déconnecter
        </button>
      </template>

      <template v-else>
        <Link href="/login" class="bg-primary hover:bg-cyan text-white px-4 py-2 rounded-xl shadow">
          Se connecter
        </Link>
        <Link href="/register" class="bg-secondary hover:bg-cyan text-white px-4 py-2 rounded-xl shadow">
          S'enregistrer
        </Link>
      </template>
    </div>
  </header>
</template>
