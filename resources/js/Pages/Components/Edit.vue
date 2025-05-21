<template>
  <div>
    <h1 class="text-xl font-bold mb-4">Modifier un composant</h1>
    <form @submit.prevent="submit">
      <!-- Champs communs -->
      <div><label>Nom</label> <input v-model="form.component.name" required /></div>
      <div><label>Marque</label> <input v-model="form.component.brand" required /></div>
      <div>
        <label>Type</label>
        <select v-model="form.component.type" required disabled>
          <option value="cpu">CPU</option>
          <option value="gpu">GPU</option>
          <option value="ram">RAM</option>
          <option value="motherboard">Carte mère</option>
          <option value="storage">Stockage</option>
          <option value="psu">Alimentation</option>
          <option value="cooler">Ventirad</option>
          <option value="case">Boîtier</option>
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

      <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">Enregistrer</button>
    </form>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  component: Object
});

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
  ram: [
    { key: 'capacity', label: 'Capacité (Go)', type: 'number', required: true },
    { key: 'speed', label: 'Vitesse (MHz)', type: 'number' },
    { key: 'ram_type', label: 'Type', type: 'text', required: true },
    { key: 'form_factor', label: 'Form Factor', type: 'text' },
    { key: 'modules', label: 'Nombre de modules', type: 'number' }
  ],
  motherboard: [
    { key: 'socket', label: 'Socket', type: 'text', required: true },
    { key: 'form_factor', label: 'Form Factor', type: 'text' },
    { key: 'ram_slots', label: 'RAM Slots', type: 'number' },
    { key: 'max_ram', label: 'Max RAM', type: 'number' },
    { key: 'chipset', label: 'Chipset', type: 'text' }
  ],
  storage: [
    { key: 'capacity', label: 'Capacité (Go)', type: 'number', required: true },
    { key: 'storage_type', label: 'Type', type: 'text', required: true },
    { key: 'form_factor', label: 'Form Factor', type: 'text' },
    { key: 'interface', label: 'Interface', type: 'text' }
  ],
  psu: [
    { key: 'wattage', label: 'Wattage', type: 'number', required: true },
    { key: 'efficiency_rating', label: 'Certification', type: 'text' },
    { key: 'modular', label: 'Modulaire (1=oui, 0=non)', type: 'checkbox' }
  ],
  cooler: [
    { key: 'cooler_type', label: 'Type de ventirad', type: 'text', required: true },
    { key: 'socket_compatibility', label: 'Compatibilité socket', type: 'text' },
    { key: 'height_mm', label: 'Hauteur (mm)', type: 'number' },
    { key: 'fan_size_mm', label: 'Taille ventilo (mm)', type: 'number' }
  ],
  case: [
    { key: 'form_factor', label: 'Form Factor', type: 'text', required: true },
    { key: 'max_gpu_length_mm', label: 'Longueur GPU max (mm)', type: 'number' },
    { key: 'max_cpu_cooler_height_mm', label: 'Hauteur ventirad max (mm)', type: 'number' },
    { key: 'drive_bays', label: 'Baies de disques', type: 'number' }
  ]
};

// Champs dynamiques pré-remplis !
const form = useForm({
  component: {
    name: props.component.name,
    brand: props.component.brand,
    type: props.component.type,
    price: props.component.price,
    img_url: props.component.img_url,
    description: props.component.description,
    release_year: props.component.release_year,
    ean: props.component.ean
  },
  // Champs spécialisés pré-remplis selon le type et les relations
  socket: props.component.cpu?.socket ?? props.component.motherboard?.socket ?? '',
  core_count: props.component.cpu?.core_count ?? '',
  thread_count: props.component.cpu?.thread_count ?? '',
  base_clock: props.component.cpu?.base_clock ?? props.component.gpu?.base_clock ?? '',
  boost_clock: props.component.cpu?.boost_clock ?? props.component.gpu?.boost_clock ?? '',
  tdp: props.component.cpu?.tdp ?? props.component.gpu?.tdp ?? '',
  integrated_graphics: props.component.cpu?.integrated_graphics ?? '',
  chipset: props.component.gpu?.chipset ?? props.component.motherboard?.chipset ?? '',
  vram: props.component.gpu?.vram ?? '',
  length_mm: props.component.gpu?.length_mm ?? '',
  capacity: props.component.ram?.capacity ?? props.component.storage?.capacity ?? '',
  speed: props.component.ram?.speed ?? '',
  ram_type: props.component.ram?.ram_type ?? '',
  form_factor: props.component.ram?.form_factor ?? props.component.motherboard?.form_factor ?? props.component.caseModel?.form_factor ?? '',
  modules: props.component.ram?.modules ?? '',
  ram_slots: props.component.motherboard?.ram_slots ?? '',
  max_ram: props.component.motherboard?.max_ram ?? '',
  storage_type: props.component.storage?.storage_type ?? '',
  interface: props.component.storage?.interface ?? '',
  wattage: props.component.psu?.wattage ?? '',
  efficiency_rating: props.component.psu?.efficiency_rating ?? '',
  modular: props.component.psu?.modular ?? '',
  cooler_type: props.component.cooler?.cooler_type ?? '',
  socket_compatibility: props.component.cooler?.socket_compatibility ?? '',
  height_mm: props.component.cooler?.height_mm ?? '',
  fan_size_mm: props.component.cooler?.fan_size_mm ?? '',
  max_gpu_length_mm: props.component.caseModel?.max_gpu_length_mm ?? '',
  max_cpu_cooler_height_mm: props.component.caseModel?.max_cpu_cooler_height_mm ?? '',
  drive_bays: props.component.caseModel?.drive_bays ?? ''
});

const fieldsForType = computed(() => typeFields[form.component.type] || []);

function submit() {
  form.put(route('components.update', props.component.id));
}
</script>
