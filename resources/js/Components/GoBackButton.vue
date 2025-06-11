<template>
  <button
    type="button"
    class="inline-flex items-center gap-2 bg-lightgray hover:bg-darkgray text-darknavy px-4 py-2 rounded-xl border shadow transition font-medium"
    @click="goBack"
  >
    <span aria-hidden="true">←</span>
    <span>Retour</span>
  </button>
</template>

<script setup>
function goBack() {
  window.history.length > 1 ? window.history.back() : window.location.assign('/')
}
</script>
