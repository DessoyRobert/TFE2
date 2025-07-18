<script setup>
import { defineProps } from 'vue'
import { router } from '@inertiajs/vue3'

const { build } = defineProps(['build'])

function showDetailPage(component) {
  if (!component || !component.id) return
  router.visit(`/components/${component.id}/details`, {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-10">
    <div class="bg-white rounded-xl shadow-md border p-8 space-y-6">
      <h1 class="text-3xl font-bold text-darknavy mb-2">
        {{ build.name }}
      </h1>
      <p class="text-gray-600 mb-4">{{ build.description }}</p>

      <div v-if="build.imgUrl" class="mb-6">
        <img :src="build.imgUrl" alt="Image du build" class="max-w-xs rounded-xl shadow" />
      </div>

      <div>
        <h2 class="text-xl font-semibold mb-2">Composants</h2>
        <ul class="space-y-1">
          <li
            v-for="component in build.components"
            :key="component.id"
            class="flex gap-2 items-center hover:text-primary transition cursor-pointer"
            @click="showDetailPage(component)"
          >
            <span v-if="component.brand" class="font-medium">{{ component.brand.name }}</span>
            <span>{{ component.name }}</span>
          </li>
        </ul>
      </div>

      <div class="text-xl font-bold mt-6">
        Prix total : <span class="text-primary">{{ build.price ? build.price + ' €' : '—' }}</span>
      </div>
    </div>
  </div>
</template>
