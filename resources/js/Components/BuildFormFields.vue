<template>
  <div class="flex flex-col space-y-2">
    <input
      v-model="buildName"
      type="text"
      placeholder="Nom du build"
      class="border px-2 py-1 rounded"
    />
    <input
      v-model="imgUrl"
      type="text"
      placeholder="Image URL (optionnel)"
      class="border px-2 py-1 rounded"
    />
    <textarea
      v-model="description"
      placeholder="Description"
      class="border px-2 py-1 rounded"
    ></textarea>
    <input
      v-model.number="price"
      type="number"
      placeholder="Prix (optionnel)"
      class="border px-2 py-1 rounded"
    />
  </div>
</template>

<script setup>
const buildName = defineModel('buildName')
const imgUrl = defineModel('imgUrl')
const description = defineModel('description')
const price = defineModel('price')
</script>

