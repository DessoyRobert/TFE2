<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Liste des composants</h1>
    <a :href="route('components.create')" class="mb-4 inline-block bg-green-600 text-white px-4 py-2 rounded">Ajouter un composant</a>
    <table>
      <thead>
        <tr>
          <th>Nom</th>
          <th>Marque</th>
          <th>Type</th>
          <th>Prix</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="component in components" :key="component.id">
          <td>{{ component.name }}</td>
          <td>{{ component.brand }}</td>
          <td>{{ component.type }}</td>
          <td>{{ component.price }}</td>
          <td>
            <a :href="route('components.edit', component.id)" class="text-blue-600 underline mr-2">Éditer</a>
            <form :action="route('components.destroy', component.id)" method="POST" style="display:inline" @submit.prevent="destroy(component.id)">
              <input type="hidden" name="_method" value="DELETE">
              <button type="submit" class="text-red-600 underline">Supprimer</button>
            </form>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

defineProps({ components: Array });

function destroy(id) {
  if (confirm('Confirmer suppression ?')) {
    router.delete(route('components.destroy', id));
  }
}
</script>
