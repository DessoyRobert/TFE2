<template>
  <div>
    <h1 class="text-xl font-bold mb-4">Ajouter un composant</h1>
    <form @submit.prevent="submit">
      <!-- Champs communs -->
      <div><label>Nom</label> <input v-model="form.component.name" required /></div>
      <div><label>Marque</label> <input v-model="form.component.brand" required /></div>
      <div>
        <label>Type</label>
        <select v-model="form.component.type" required>
          <option value="">Sélectionner un type</option>
          <option value="cpu">CPU</option>
          <option value="gpu">GPU</option>
          <option value="ram">RAM</option>
          <option value="motherboard">Carte mère</option>
          <option value="storage">Stockage</option>
          <option value="psu">Alimentation</option>
          <option value="cooler">Ventirad</option>
          <option value="case">Boitier</option>
        </select>
      </div>
      <div><label>Prix</label> <input v-model="form.component.price" type="number" step="0.01" /></div>
      <div><label>Année de sortie</label> <input v-model="form.component.release_year" type="number" /></div>
      <div><label>Description</label> <input v-model="form.component.description" /></div>
      <div><label>EAN</label> <input v-model="form.component.ean" /></div>

      <!-- Champs dynamiques selon le type -->
      <div v-for="field in fieldsForType" :key="field.key">
        <label :for="field.key">{{ field.label }}</label>
        <input
          v-model="form[field.key]"
          :type="field.type"
          :id="field.key"
          :required="field.required"
        />
      </div>

      <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">Ajouter</button>
      <button style="background:red; color:yellow; font-size:1.5rem;">
  TEST BOUTON
</button>
    </form>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

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
  // Ajoute RAM, motherboard, etc.
};

const form = useForm({
  component: {
    name: '',
    brand: '',
    type: '',
    price: '',
    img_url: '',
    description: '',
    release_year: '',
    ean: ''
  },
  // Tous les champs dynamiques possibles
  socket: '', core_count: '', thread_count: '', base_clock: '', boost_clock: '', tdp: '', integrated_graphics: '',
  chipset: '', vram: '', length_mm: ''
  // ...etc pour les autres types
});

const fieldsForType = computed(() => typeFields[form.component.type] || []);

function submit() {
  form.post(route('components.store'));
}
</script>
