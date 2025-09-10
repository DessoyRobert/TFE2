<script setup>
// Props reçues depuis le parent via v-model
defineProps({
  name: String,
  description: String,
  imgUrl: String,
  autoPrice: Number
})

// Emission des mises à jour pour les champs bindés
defineEmits(['update:name', 'update:description', 'update:imgUrl'])
</script>

<template>
  <div class="flex flex-col space-y-4">
    <!-- Champ : nom du build -->
    <div>
      <label class="block text-sm font-semibold text-darknavy mb-1">Nom du build</label>
      <input
        :value="name"
        @input="$emit('update:name', $event.target.value)"
        type="text"
        placeholder="Nom du build"
        class="w-full border px-3 py-2 rounded-lg shadow-sm"
      />
    </div>

    <!-- Champ : image (URL) -->
    <div>
      <label class="block text-sm font-semibold text-darknavy mb-1">URL de l’image (optionnel)</label>
      <input
        :value="imgUrl"
        @input="$emit('update:imgUrl', $event.target.value)"
        type="text"
        placeholder="Lien vers une image illustrative"
        class="w-full border px-3 py-2 rounded-lg shadow-sm"
      />
    </div>

    <!-- Champ : description -->
    <div>
      <label class="block text-sm font-semibold text-darknavy mb-1">Description</label>
      <textarea
        :value="description"
        @input="$emit('update:description', $event.target.value)"
        placeholder="Détails, objectifs ou particularités du build"
        class="w-full border px-3 py-2 rounded-lg shadow-sm"
        rows="4"
      ></textarea>
    </div>

    <!-- Affichage : prix auto (lecture seule) -->
    <div>
      <label class="block text-sm font-semibold text-darknavy mb-1">Prix total estimé</label>
      <div class="w-full px-3 py-2 border rounded-lg bg-lightgray text-darkgray">
        {{ autoPrice?.toFixed(2) ?? '0.00' }} €
      </div>
    </div>
  </div>
</template>
