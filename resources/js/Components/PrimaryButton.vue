<template>
  <button
    class="mt-4 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded"
    @click="$emit('click')"
  >
    <slot />
  </button>
</template>
