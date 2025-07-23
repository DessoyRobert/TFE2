<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'

const page = usePage()
const flash = computed(() => page.props?.flash || {})
const success = computed(() => flash.value?.success || null)
const uploadedUrl = computed(() => flash.value?.url || null)

const preview = ref(null)

const form = useForm({
  image: null,
  target_type: 'component', // ou 'build'
  target_id: null
})

function handleFileChange(e) {
  const file = e.target.files[0]
  form.image = file
  preview.value = file ? URL.createObjectURL(file) : null
}

function submit() {
  form.post(route('admin.images.store'), {
    forceFormData: true,
    preserveScroll: true
  })
}
</script>

<template>
  <div class="max-w-xl mx-auto p-6 bg-white shadow rounded-2xl">
    <h1 class="text-xl font-semibold mb-4">Uploader une image (Cloudinary)</h1>

    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">Associer à :</label>
      <select v-model="form.target_type" class="w-full border border-gray-300 rounded px-2 py-1">
        <option value="component">Composant</option>
        <option value="build">Build</option>
      </select>
    </div>

    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">ID de la cible :</label>
      <input type="number" v-model="form.target_id" class="w-full border border-gray-300 rounded px-2 py-1" />
    </div>

    <input
      type="file"
      accept="image/*"
      @change="handleFileChange"
      class="mb-2 block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
    />

    <div v-if="preview" class="mb-4">
      <p class="text-sm text-gray-500 mb-1">Aperçu :</p>
      <img :src="preview" :key="preview" alt="Preview" class="max-w-full rounded-md border border-gray-300" />
    </div>

    <div v-if="form.errors.image" class="text-red-500 text-sm mb-2">
      ⚠️ {{ form.errors.image }}
    </div>

    <button
      @click="submit"
      :disabled="form.processing"
      class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
    >
      {{ form.processing ? 'Envoi...' : 'Uploader' }}
    </button>

    <div v-if="success" class="mt-4 text-green-600 font-medium">
      ✅ {{ success }}
    </div>

    <div v-if="uploadedUrl" class="mt-2">
      📸
      <a :href="uploadedUrl" target="_blank" class="text-blue-600 underline">
        Voir sur Cloudinary
      </a>
    </div>
  </div>
</template>
