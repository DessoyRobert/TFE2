<script setup>
const name = defineModel('name')
const imgUrl = defineModel('imgUrl')
const description = defineModel('description')
const props = defineProps({ autoPrice: { type: Number, default: 0 } })
</script>

<template>
  <div class="flex flex-col space-y-2">
    <input
      v-model="name"
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
      type="number"
      :value="autoPrice"
      readonly
      class="border px-2 py-1 rounded"
      placeholder="Prix (auto)"
    />
  </div>
</template>
