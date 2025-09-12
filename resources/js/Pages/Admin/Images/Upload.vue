<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'

function transformCloudinary(url, w = 480) {
  if (!url) return url
  return url.includes('/upload/')
    ? url.replace('/upload/', `/upload/f_auto,q_auto${w ? ',w_' + Number(w) : ''}/`)
    : url
}

const page = usePage()
const flash = computed(() => page.props?.flash || {})
const success = computed(() => flash.value?.success || null)
const uploadedUrl = computed(() => flash.value?.url || null)

const source = ref('file') // 'file' | 'url'
const preview = ref(null)
let previewObjectUrl = null

const form = useForm({
  image: null,        // fichier
  image_url: '',      // url distante
  target_type: 'component',
  target_id: null
})

const canSubmit = computed(() => {
  const haveTarget = !!form.target_type && !!form.target_id
  const haveFile = source.value === 'file' && !!form.image
  const haveUrl  = source.value === 'url' && !!form.image_url
  return haveTarget && (haveFile || haveUrl) && !form.processing
})

function setFile(file) {
  form.image = file || null
  if (previewObjectUrl) {
    URL.revokeObjectURL(previewObjectUrl)
    previewObjectUrl = null
  }
  if (file) {
    previewObjectUrl = URL.createObjectURL(file)
    preview.value = previewObjectUrl
  } else {
    preview.value = null
  }
}

function onFileChange(e) {
  const file = e.target.files?.[0]
  setFile(file)
}
function onDrop(e) {
  e.preventDefault()
  const file = e.dataTransfer?.files?.[0]
  setFile(file)
}
function onDragOver(e) { e.preventDefault() }

function submit() {
  // si URL sélectionnée, s’assurer qu’on n’envoie pas d’ancienne image locale
  if (source.value === 'url') setFile(null)

  form.post(route('admin.images.store'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      setFile(null)
      // on garde target_type/id pour enchaîner plusieurs uploads
      form.reset('image', 'image_url')
    }
  })
}

onBeforeUnmount(() => {
  if (previewObjectUrl) {
    URL.revokeObjectURL(previewObjectUrl)
    previewObjectUrl = null
  }
})
</script>

<template>
  <div class="max-w-xl mx-auto p-6 bg-white shadow rounded-2xl">
    <h1 class="text-xl font-semibold mb-4">Uploader une image (Cloudinary)</h1>

    <!-- Ciblage -->
    <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Associer à</label>
        <select v-model="form.target_type" class="w-full border border-gray-300 rounded px-2 py-2">
          <option value="component">Composant</option>
          <option value="build">Build</option>
        </select>
        <p v-if="form.errors.target_type" class="text-red-500 text-xs mt-1">⚠️ {{ form.errors.target_type }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">ID de la cible</label>
        <input
          type="number"
          v-model.number="form.target_id"
          min="1"
          class="w-full border border-gray-300 rounded px-2 py-2"
          placeholder="ex: 12"
        />
        <p v-if="form.errors.target_id" class="text-red-500 text-xs mt-1">⚠️ {{ form.errors.target_id }}</p>
      </div>
    </div>

    <!-- Source -->
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
      <div class="flex gap-4">
        <label class="inline-flex items-center gap-2">
          <input type="radio" value="file" v-model="source" />
          <span>Fichier</span>
        </label>
        <label class="inline-flex items-center gap-2">
          <input type="radio" value="url" v-model="source" />
          <span>URL</span>
        </label>
      </div>
    </div>

    <!-- Fichier -->
    <template v-if="source === 'file'">
      <div
        class="mb-3 border-2 border-dashed rounded-lg p-4 text-center cursor-pointer hover:bg-gray-50"
        @drop="onDrop" @dragover="onDragOver"
        @click="$refs.fileInput?.click()"
      >
        <p class="text-sm text-gray-600">Glissez-déposez une image, ou cliquez pour choisir un fichier</p>
        <input ref="fileInput" type="file" accept="image/*" @change="onFileChange" class="hidden" />
      </div>

      <div v-if="preview" class="mb-4">
        <p class="text-sm text-gray-500 mb-1">Aperçu local :</p>
        <img :src="preview" :key="preview" alt="Preview" class="max-w-full rounded-md border border-gray-200" />
      </div>

      <div v-if="form.errors.image" class="text-red-500 text-sm mb-2">
        ⚠️ {{ form.errors.image }}
      </div>
    </template>

    <!-- URL -->
    <template v-else>
      <div class="mb-3">
        <label class="block text-sm font-medium text-gray-700 mb-1">URL de l’image</label>
        <input
          type="url"
          v-model.trim="form.image_url"
          placeholder="https://exemple.com/monimage.jpg"
          class="w-full border border-gray-300 rounded px-2 py-2"
        />
        <p class="text-xs text-gray-500 mt-1">
          Astuce : une URL d’image Cloudinary sera rapatriée et stockée dans votre Cloudinary.
        </p>
      </div>
      <div v-if="form.errors.image_url" class="text-red-500 text-sm mb-2">
        ⚠️ {{ form.errors.image_url }}
      </div>
    </template>

    <!-- Action -->
    <button
      @click="submit"
      :disabled="!canSubmit"
      class="bg-blue-600 disabled:bg-blue-300 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
    >
      {{ form.processing ? 'Envoi...' : 'Uploader' }}
    </button>

    <!-- Flash success -->
    <div v-if="success" class="mt-4 text-green-600 font-medium">
      ✅ {{ success }}
    </div>

    <!-- Lien Cloudinary + miniature -->
    <div v-if="uploadedUrl" class="mt-2">
      📸
      <a :href="uploadedUrl" target="_blank" class="text-blue-600 underline">
        Voir sur Cloudinary
      </a>
      <div class="mt-2">
        <img :src="transformCloudinary(uploadedUrl)" class="max-w-full rounded-md border border-gray-200" />
      </div>
    </div>
  </div>
</template>
