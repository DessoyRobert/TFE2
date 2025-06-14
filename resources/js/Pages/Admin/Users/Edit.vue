<script setup>
import { useForm, router } from '@inertiajs/vue3'
import GoBackButton from '@/Components/GoBackButton.vue'

const props = defineProps({
  user: {
    type: Object,
    required: true,
  }
})

// Formulaire édition avec Inertia (nom, email, is_admin)
const form = useForm({
  name: props.user.name,
  email: props.user.email,
  is_admin: !!props.user.is_admin,
})

function submit() {
  form.put(route('admin.users.update', props.user.id), {
    onSuccess: () => {
      // Optionnel : retour à la liste ou notif
      router.visit(route('admin.users.index'))
    }
  })
}
</script>

<template>
  <div class="max-w-lg mx-auto py-10">
    <GoBackButton class="mb-4" />
    <h1 class="text-2xl font-bold text-darknavy mb-6">Éditer l’utilisateur</h1>

    <form @submit.prevent="submit" class="space-y-4 bg-white p-6 rounded-xl shadow">
      <div>
        <label class="block text-sm font-semibold mb-1">Nom</label>
        <input
          v-model="form.name"
          class="w-full border rounded-xl px-3 py-2"
          required
        />
        <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">Email</label>
        <input
          v-model="form.email"
          type="email"
          class="w-full border rounded-xl px-3 py-2"
          required
        />
        <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">{{ form.errors.email }}</div>
      </div>
      <div class="flex items-center gap-2">
        <input type="checkbox" id="is_admin" v-model="form.is_admin" class="rounded" />
        <label for="is_admin" class="select-none">Admin</label>
      </div>

      <div class="pt-4 flex gap-4">
        <button
          type="submit"
          class="bg-darknavy text-white px-6 py-2 rounded-xl shadow font-semibold hover:bg-primary transition"
          :disabled="form.processing"
        >
          Sauvegarder
        </button>
        <GoBackButton />
      </div>
    </form>
  </div>
</template>
