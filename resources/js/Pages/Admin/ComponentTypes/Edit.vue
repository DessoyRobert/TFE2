<script setup>
/*
  Page d'édition d'un "ComponentType" (Admin)
  Hypothèses de routes Ziggy :
    - admin.component-types.update (PUT /admin/component-types/{id})
    - admin.component-types.index  (GET /admin/component-types)
  Adapter les noms si nécessaire.
*/

import { computed, ref, onBeforeUnmount } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'

const props = defineProps({
  componentType: {
    type: Object,
    required: true, // { id, name, ... }
  },
  // Optionnel : permet de surcharger les noms Ziggy si ton projet diffère
  routes: {
    type: Object,
    default: () => ({
      update: 'admin.component-types.update',
      index: 'admin.component-types.index',
    }),
  },
})

/* ---------------------------------------------
   Formulaire
--------------------------------------------- */
const form = useForm({
  name: props.componentType?.name ?? '',
})

const isDirty = computed(() => form.isDirty)
const saving = computed(() => form.processing)
const canSubmit = computed(() => form.name?.trim().length > 0 && !saving.value)

/* ---------------------------------------------
   Navigation sécurisée si modifications non sauvegardées
--------------------------------------------- */
const confirmLeave = (e) => {
  if (!isDirty.value || saving.value) return
  e.preventDefault()
  e.returnValue = ''
}
window.addEventListener('beforeunload', confirmLeave)
onBeforeUnmount(() => window.removeEventListener('beforeunload', confirmLeave))

/* ---------------------------------------------
   Soumission
--------------------------------------------- */
function submit() {
  if (!props.componentType?.id) return

  form.clearErrors()

  form.put(route(props.routes.update, props.componentType.id), {
    preserveScroll: true,
    onSuccess: () => {
      // Option : rester sur la page et afficher un toast serveur
      // Ici, on reste et on enlève l'état "dirty" en resynchronisant la valeur
      // Inertia fera déjà un refresh des props si le contrôleur renvoie l'entité à jour.
    },
    onError: () => {
      // Les messages d'erreurs champs seront disponibles dans form.errors
    },
  })
}

/* ---------------------------------------------
   Retour à l'index (avec confirmation si dirty)
--------------------------------------------- */
function goIndex() {
  if (isDirty.value && !confirm('Des modifications non sauvegardées seront perdues. Continuer ?')) return
  router.visit(route(props.routes.index))
}
</script>

<template>
  <div class="max-w-3xl mx-auto p-6 lg:p-8">
    <!-- En-tête + fil d'Ariane minimal -->
    <nav class="text-sm mb-4 text-gray-500">
      <span class="hover:underline cursor-pointer" @click="goIndex">Types de composants</span>
      <span class="mx-2">/</span>
      <span class="text-gray-800">Éditer</span>
    </nav>

    <header class="mb-6">
      <h1 class="text-2xl font-semibold text-darknavy">Éditer le type de composant</h1>
      <p class="text-gray-600 mt-1">
        ID: <span class="font-mono">{{ componentType?.id }}</span>
      </p>
    </header>

    <div class="bg-white rounded-2xl shadow p-5 space-y-5">
      <form @submit.prevent="submit" class="space-y-5">
        <!-- Champ : Nom -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
          <input
            id="name"
            v-model.trim="form.name"
            type="text"
            class="w-full rounded-xl border border-gray-300 focus:border-primary focus:ring-1 focus:ring-primary px-3 py-2"
            :class="{'border-red-500': form.errors.name}"
            placeholder="Ex. CPU, GPU, RAM"
            autocomplete="off"
          />
          <p v-if="form.errors.name" class="text-red-600 text-sm mt-1">
            {{ form.errors.name }}
          </p>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2 pt-2">
          <button
            type="submit"
            :disabled="!canSubmit"
            class="px-4 py-2 rounded-2xl bg-darknavy text-white disabled:opacity-50"
          >
            <span v-if="saving">Enregistrement...</span>
            <span v-else>Enregistrer</span>
          </button>

          <button
            type="button"
            @click="goIndex"
            class="px-4 py-2 rounded-2xl border border-gray-300 hover:bg-gray-50"
          >
            Annuler
          </button>

          <span
            v-if="isDirty && !saving"
            class="text-xs text-amber-600 ml-2"
            aria-live="polite"
          >
            Modifications non sauvegardées
          </span>
        </div>
      </form>
    </div>

    <!-- Aide / Informations -->
    <div class="mt-4 text-xs text-gray-500">
      Astuce : assure-toi que le nom est unique si le backend l’exige.
    </div>
  </div>
</template>

<style scoped>
</style>
