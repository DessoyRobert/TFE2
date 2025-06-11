<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import GoBackButton from '@/Components/GoBackButton.vue'
const props = defineProps({
  build: Object,
  allComponents: Array
})

// On mappe les composants actuels du build pour remplir le formulaire
const selectedComponents = ref(
  props.build.components.map(c => ({
    component_id: c.id,
    name: c.name,
    quantity: c.pivot?.quantity ?? 1
  }))
)

// Formulaire d’édition du build
const form = useForm({
  name: props.build.name ?? '',
  description: props.build.description ?? '',
  price: props.build.price ?? '',
  img_url: props.build.img_url ?? '',
  components: selectedComponents.value
})

// Pour le select de composants (tu pourrais filtrer/afficher plus joliment par type/brand)
const availableComponents = computed(() => props.allComponents)

function addComponent() {
  form.components.push({ component_id: '', name: '', quantity: 1 })
}
function removeComponent(idx) {
  form.components.splice(idx, 1)
}

function submit() {
  form.put(route('admin.builds.update', props.build.id))
}
</script>

<template>
  <div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl shadow-lg border"><GoBackButton class="mb-4" />
    <h1 class="text-2xl font-bold text-darknavy mb-6">Éditer le build</h1>
    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block font-semibold mb-1">Nom</label>
        <input v-model="form.name" required class="w-full border rounded-xl px-3 py-2" />
      </div>
      <div>
        <label class="block font-semibold mb-1">Description</label>
        <textarea v-model="form.description" class="w-full border rounded-xl px-3 py-2" />
      </div>
      <div>
        <label class="block font-semibold mb-1">Prix</label>
        <input v-model="form.price" type="number" class="w-full border rounded-xl px-3 py-2" />
      </div>
      <div>
        <label class="block font-semibold mb-1">Image (URL)</label>
        <input v-model="form.img_url" class="w-full border rounded-xl px-3 py-2" />
      </div>

      <div class="space-y-2">
        <label class="block font-semibold mb-1">Composants du build</label>
        <div v-for="(c, idx) in form.components" :key="idx" class="flex gap-2 mb-2 items-center">
          <select v-model="c.component_id" required class="border rounded-xl px-3 py-2 w-2/3">
            <option value="">Sélectionner un composant</option>
            <option v-for="comp in availableComponents" :value="comp.id">
              {{ comp.name }} ({{ comp.brand?.name ?? '' }} - {{ comp.type?.name ?? '' }})
            </option>
          </select>
          <input v-model="c.quantity" type="number" min="1" class="border rounded-xl px-3 py-2 w-20" />
          <button type="button" @click="removeComponent(idx)" class="text-red-600 px-2 font-bold">x</button>
        </div>
        <button type="button" @click="addComponent" class="bg-primary hover:bg-cyan text-white px-4 py-1 rounded-xl shadow text-sm">Ajouter un composant</button>
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <button
          type="button"
          class="bg-lightgray text-darknavy px-4 py-2 rounded-xl border"
          @click="$inertia.visit(route('admin.builds.index'))"
        >Annuler</button>
        <button
          type="submit"
          class="bg-primary hover:bg-cyan text-white px-6 py-2 rounded-xl shadow font-semibold"
        >Enregistrer</button>
      </div>
    </form>
  </div>
</template>
