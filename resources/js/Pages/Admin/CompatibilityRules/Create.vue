<script setup>
import { useForm, Link } from '@inertiajs/vue3'

defineProps({
  componentTypes: Array
})

const form = useForm({
  component_type_a_id: '',
  field_a: '',
  operator: '',
  field_b: '',
  component_type_b_id: '',
  rule_type: 'hard',
  description: ''
})

function submit() {
  form.post('/admin/compatibility-rules')
}
</script>

<template>
  <div class="p-6 max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Ajouter une règle de compatibilité</h1>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block font-medium">Type A</label>
        <select v-model="form.component_type_a_id" class="w-full border rounded p-2">
          <option value="" disabled>-- Sélectionner --</option>
          <option v-for="type in componentTypes" :key="type.id" :value="type.id">
            {{ type.name }}
          </option>
        </select>
      </div>

      <div>
        <label class="block font-medium">Champ A</label>
        <input type="text" v-model="form.field_a" class="w-full border rounded p-2" />
      </div>

      <div>
        <label class="block font-medium">Opérateur</label>
        <select v-model="form.operator" class="w-full border rounded p-2">
        <option value="">-- Sélectionner --</option>
        <option value="=">Égal (=)</option>
        <option value="!=">Différent (!=)</option>
        <option value=">">Supérieur (>)</option>
        <option value="<">Inférieur (<)</option>
        <option value=">=">Supérieur ou égal (>=)</option>
        <option value="<=">Inférieur ou égal (<=)</option>
        <option value="in">Contient (in)</option>
        <option value="like">Like</option>
        </select>
      </div>

      <div>
        <label class="block font-medium">Champ B</label>
        <input type="text" v-model="form.field_b" class="w-full border rounded p-2" />
      </div>

      <div>
        <label class="block font-medium">Type B</label>
        <select v-model="form.component_type_b_id" class="w-full border rounded p-2">
          <option value="" disabled>-- Sélectionner --</option>
          <option v-for="type in componentTypes" :key="type.id" :value="type.id">
            {{ type.name }}
          </option>
        </select>
      </div>

      <div>
        <label class="block font-medium">Type de règle</label>
        <select v-model="form.rule_type" class="w-full border rounded p-2">
          <option value="hard">Hard</option>
          <option value="soft">Soft</option>
        </select>
      </div>

      <div>
        <label class="block font-medium">Description</label>
        <textarea v-model="form.description" class="w-full border rounded p-2" />
      </div>

      <div class="flex gap-4">
        <button
          type="submit"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
        >
          Enregistrer
        </button>
        <Link href="/admin/compatibility-rules" class="text-gray-600 hover:underline">Annuler</Link>
      </div>
    </form>
  </div>
</template>
