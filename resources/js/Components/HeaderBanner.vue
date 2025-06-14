<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth.user)

function logout() {
  router.post(route('logout'))
}
</script>

<template>
  <header class="flex items-center justify-between px-6 py-4 bg-darknavy shadow-md rounded-b-2xl">
    <Link href="/" class="text-2xl font-bold text-primary hover:text-cyan transition-all select-none">
      JarvisTech <span class="text-violetdark">/ PCBuilder</span>
    </Link>

    <div class="flex items-center gap-4">
      <template v-if="user">
        <Link href="/profile" class="text-white hover:text-cyan">
          {{ user.name }}
        </Link>
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
      </template>
    </div>
  </header>
</template>
