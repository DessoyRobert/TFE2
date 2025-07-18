<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({
  rules: Array
})

const deleting = ref(null)

function confirmDelete(ruleId) {
  if (confirm('Supprimer cette règle ?')) {
    router.delete(`/admin/compatibility-rules/${ruleId}`, {
      onBefore: () => (deleting.value = ruleId),
      onFinish: () => (deleting.value = null)
    })
  }
}
</script>

<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Règles de compatibilité</h1>
      <Link
        href="/admin/compatibility-rules/create"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
      >
        + Ajouter une règle
      </Link>
    </div>

    <div class="overflow-x-auto border rounded">
      <table class="min-w-full table-auto text-left">
        <thead class="bg-gray-100 border-b">
          <tr>
            <th class="p-2">Type A</th>
            <th class="p-2">Champ A</th>
            <th class="p-2">Opérateur</th>
            <th class="p-2">Champ B</th>
            <th class="p-2">Type B</th>
            <th class="p-2">Type de règle</th>
            <th class="p-2">Description</th>
            <th class="p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="rule in rules"
            :key="rule.id"
            class="border-b hover:bg-gray-50 transition"
          >
            <td class="p-2">{{ rule.component_type_a?.name }}</td>
            <td class="p-2">{{ rule.field_a }}</td>
            <td class="p-2">{{ rule.operator }}</td>
            <td class="p-2">{{ rule.field_b }}</td>
            <td class="p-2">{{ rule.component_type_b?.name }}</td>
            <td class="p-2">{{ rule.rule_type }}</td>
            <td class="p-2">{{ rule.description }}</td>
            <td class="p-2 flex gap-2">
              <Link
                :href="`/admin/compatibility-rules/${rule.id}/edit`"
                class="text-blue-600 hover:underline"
              >
                Modifier
              </Link>
              <button
                @click="confirmDelete(rule.id)"
                class="text-red-600 hover:underline"
              >
                Supprimer
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
