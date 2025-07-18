<script setup>
import { usePage } from '@inertiajs/vue3'
import { ref } from 'vue'

const user = usePage().props.auth.user
const name = ref(user.name)
const email = ref(user.email)
</script>

<template>
  <div class="p-8 max-w-2xl mx-auto rounded-2xl shadow-md">
    <h1 class="text-3xl font-bold text-primary mb-6">Mon Profil</h1>

    <form class="space-y-6"> 
      <div>
        <label for="name" class="block text-sm font-medium text-darknavy">Nom</label>
        <input
          v-model="name"
          type="text"
          id="name"
          class="mt-1 block w-full rounded-xl border-gray-700 bg-gray-800 text-white focus:ring-cyan focus:border-cyan"
        />
      </div>

      <div>
        <label for="email" class="block text-sm font-medium text-darknavy">Email</label>
        <input
          v-model="email"
          type="email"
          id="email"
          class="mt-1 block w-full rounded-xl border-gray-700 bg-gray-800 text-white focus:ring-cyan focus:border-cyan"
        />
      </div>

      <div class="pt-4">
        <button
          type="submit"
          class="px-6 py-2 bg-primary text-white rounded-xl hover:bg-cyan transition"
        >
          Sauvegarder
        </button>
      </div>
    </form>
  </div>
</template>
