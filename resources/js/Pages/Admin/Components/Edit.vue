<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import GoBackButton from '@/Components/GoBackButton.vue'
const props = defineProps({
  brands: { type: Array, required: true },
  component_types: { type: Array, required: true },
  component: { type: Object, required: true },
  specific: { type: Object, required: false }
})

// Définition des champs dynamiques pour chaque type
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
  // ... rajoute tes autres types ici
}

// Initialisation du formulaire : données communes + spécifiques si dispo
const form = useForm({
  // Champs communs
  name: props.component.name ?? '',
  brand_id: props.component.brand_id ?? '',
  component_type_id: props.component.component_type_id ?? '',
  price: props.component.price ?? '',
  img_url: props.component.img_url ?? '',
  description: props.component.description ?? '',
  release_year: props.component.release_year ?? '',
  ean: props.component.ean ?? '',
  // Champs dynamiques (cpu/gpu/...)
  socket: props.specific?.socket ?? '',
  core_count: props.specific?.core_count ?? '',
  thread_count: props.specific?.thread_count ?? '',
  base_clock: props.specific?.base_clock ?? '',
  boost_clock: props.specific?.boost_clock ?? '',
  tdp: props.specific?.tdp ?? '',
  integrated_graphics: props.specific?.integrated_graphics ?? '',
  chipset: props.specific?.chipset ?? '',
  vram: props.specific?.vram ?? '',
  length_mm: props.specific?.length_mm ?? ''
  // ...etc pour les autres champs dynamiques
})

// Calcul du type sélectionné pour afficher les bons champs dynamiques
const fieldsForType = computed(() => {
  const t = props.component_types.find(tt => tt.id == form.component_type_id)
  return t ? (typeFields[t.name] ?? []) : []
})

// Soumission du formulaire
function submit() {
  form.put(route('admin.components.update', props.component.id))
}
</script>

<template>
  <div class="max-w-2xl mx-auto bg-white p-8 rounded-2xl shadow-lg border"><GoBackButton class="mb-4" />
    <h1 class="text-2xl font-bold text-darknavy mb-6">Modifier un composant</h1>

    <!-- Affichage des erreurs -->
    <div v-if="form.errors && Object.keys(form.errors).length" class="mb-4">
      <ul class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl space-y-1">
        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
      </ul>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <!-- Champs communs -->
      <div>
        <label class="block font-semibold mb-1">Nom</label>
        <input v-model="form.name" required class="w-full border rounded-xl px-3 py-2" />
      </div>
      <div>
        <label class="block font-semibold mb-1">Marque</label>
        <select v-model="form.brand_id" required class="w-full border rounded-xl px-3 py-2">
          <option value="">Sélectionner une marque</option>
          <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
        </select>
      </div>
      <div>
        <label class="block font-semibold mb-1">Type</label>
        <select v-model="form.component_type_id" required class="w-full border rounded-xl px-3 py-2">
          <option value="">Sélectionner un type</option>
          <option v-for="t in component_types" :key="t.id" :value="t.id">{{ t.name }}</option>
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

      <!-- Champs dynamiques selon le type -->
      <div v-for="field in fieldsForType" :key="field.key">
        <label class="block font-semibold mb-1">{{ field.label }}</label>
        <input
          v-model="form[field.key]"
          :type="field.type"
          :required="field.required"
          class="w-full border rounded-xl px-3 py-2"
        />
      </div>

      <!-- Boutons d’action -->
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
          Enregistrer les modifications
        </button>
      </div>
    </form>
  </div>
</template>
