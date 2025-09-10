<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import GoBackButton from '@/Components/GoBackButton.vue'
// Props reçues du contrôleur Inertia
const props = defineProps({
  brands: { type: Array, required: true },
  component_types: { type: Array, required: true },
  categories: { type: Array, required: false }
})

// Définition des champs dynamiques selon le type de composant
const typeFields = {
  cpu: [
    { key: 'socket', label: 'Socket', type: 'text', required: true },
    { key: 'core_count', label: 'Cœurs', type: 'number', required: true },
    { key: 'thread_count', label: 'Threads', type: 'number', required: true },
    { key: 'base_clock', label: 'Base Clock (GHz)', type: 'number' },
    { key: 'boost_clock', label: 'Boost Clock (GHz)', type: 'number' },
    { key: 'tdp', label: 'TDP (W)', type: 'number' },
    { key: 'integrated_graphics', label: 'IGPU', type: 'text' }
  ],
  gpu: [
    { key: 'chipset', label: 'Chipset', type: 'text', required: true },
    { key: 'vram', label: 'VRAM (Go)', type: 'number', required: true },
    { key: 'base_clock', label: 'Base Clock (MHz)', type: 'number' },
    { key: 'boost_clock', label: 'Boost Clock (MHz)', type: 'number' },
    { key: 'tdp', label: 'TDP (W)', type: 'number' },
    { key: 'length_mm', label: 'Longueur (mm)', type: 'number' }
  ],
  // Ajoute ici les autres types (ram, motherboard, ...)
}

const form = useForm({
  name: '',
  brand_id: '',
  component_type_id: '',
  price: '',
  img_url: '',
  description: '',
  release_year: '',
  ean: '',
  // Champs dynamiques
  socket: '', core_count: '', thread_count: '', base_clock: '', boost_clock: '', tdp: '', integrated_graphics: '',
  chipset: '', vram: '', length_mm: ''
  // ...Ajoute ici tous les autres champs potentiels
})

const fieldsForType = computed(() => {
  const typeObj = props.component_types.find(t => t.id == form.component_type_id)
  return typeObj ? typeFields[typeObj.name] || [] : []
})

function submit() {
  form.post(route('admin.components.store'))
}
</script>

<template>
  <div class="max-w-2xl mx-auto bg-white p-8 rounded-2xl shadow-lg border"><GoBackButton class="mb-4" />
    <h1 class="text-2xl font-bold text-darknavy mb-6">Ajouter un composant</h1>
    <div v-if="form.errors && Object.keys(form.errors).length" class="mb-4">
      <ul class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl space-y-1">
        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
      </ul>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block font-semibold mb-1">Nom</label>
        <input v-model="form.name" required class="w-full border rounded-xl px-3 py-2" />
      </div>
      <div>
        <label class="block font-semibold mb-1">Marque</label>
        <select v-model="form.brand_id" required class="w-full border rounded-xl px-3 py-2">
          <option value="">Sélectionner une marque</option>
          <option v-for="b in props.brands" :key="b.id" :value="b.id">{{ b.name }}</option>
        </select>
      </div>
      <div>
        <label class="block font-semibold mb-1">Type</label>
        <select v-model="form.component_type_id" required class="w-full border rounded-xl px-3 py-2">
          <option value="">Sélectionner un type</option>
          <option v-for="t in props.component_types" :key="t.id" :value="t.id">{{ t.name }}</option>
        </select>
      </div>
      <div>
        <label class="block font-semibold mb-1">Prix</label>
        <input v-model="form.price" type="number" step="0.01" class="w-full border rounded-xl px-3 py-2" />
      </div>
      <div>
        <label class="block font-semibold mb-1">Année de sortie</label>
        <input v-model="form.release_year" type="number" class="w-full border rounded-xl px-3 py-2" />
      </div>
      <div>
        <label class="block font-semibold mb-1">Description</label>
        <input v-model="form.description" class="w-full border rounded-xl px-3 py-2" />
      </div>
      <div>
        <label class="block font-semibold mb-1">EAN</label>
        <input v-model="form.ean" class="w-full border rounded-xl px-3 py-2" />
      </div>
      <div>
        <label class="block font-semibold mb-1">Image (URL)</label>
        <input v-model="form.img_url" class="w-full border rounded-xl px-3 py-2" />
      </div>

      <!-- Champs dynamiques selon le type choisi -->
      <div v-for="field in fieldsForType" :key="field.key">
        <label class="block font-semibold mb-1">{{ field.label }}</label>
        <input
          v-model="form[field.key]"
          :type="field.type"
          :required="field.required"
          class="w-full border rounded-xl px-3 py-2"
        />
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <button
          type="button"
          class="bg-lightgray text-darknavy px-4 py-2 rounded-xl border"
          @click="$inertia.visit(route('admin.components.index'))"
        >
          Annuler
        </button>
        <button
          type="submit"
          class="bg-primary hover:bg-cyan text-white px-6 py-2 rounded-xl shadow font-semibold"
        >
          Ajouter le composant
        </button>
      </div>
    </form>
  </div>
</template>
