<script setup>
import { useForm } from '@inertiajs/vue3'
import GoBackButton from '@/Components/GoBackButton.vue'
const props = defineProps({
  brand: { type: Object, required: true }
})

const form = useForm({
  name: props.brand.name ?? ''
})

function submit() {
  form.put(route('admin.brands.update', props.brand.id))
}
</script>

<template>
  <div class="max-w-xl mx-auto py-10">
    <GoBackButton class="mb-4" />
    <h1 class="text-2xl font-bold text-darknavy mb-6">Modifier une marque</h1>

    <div v-if="Object.keys(form.errors).length" class="mb-4">
      <ul class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl space-y-1">
        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
      </ul>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block font-semibold mb-1">Nom de la marque</label>
        <input v-model="form.name" required class="w-full border rounded-xl px-3 py-2" />
      </div>
      <div class="flex justify-end gap-3 mt-6">
        <button
          type="button"
          class="bg-lightgray text-darknavy px-4 py-2 rounded-xl border"
          @click="$inertia.visit(route('admin.brands.index'))"
        >
          Annuler
        </button>
        <button
          type="submit"
          class="bg-primary hover:bg-cyan text-white px-6 py-2 rounded-xl shadow font-semibold"
        >
          Enregistrer
        </button>
      </div>
    </form>
  </div>
</template>
