<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useBuildStore } from '@/stores/buildStore'
const build = useBuildStore()

const total = computed(() => build.totalPrice)
const count = computed(() => Object.values(build.build).filter(Boolean).length)

function goBuild() { router.visit('/builds/create') }
</script>

<template>
  <div
    v-show="count > 0"
    class="fixed bottom-4 left-1/2 -translate-x-1/2 z-40
           bg-white/95 backdrop-blur border border-slate-200
           shadow-2xl rounded-2xl px-5 py-3 flex items-center gap-4">
    <div class="text-sm text-slate-700">
      <b>{{ count }}</b> composant(s) sélectionnés —
      <b>{{ total.toFixed(2) }} €</b>
    </div>
    <button
      class="px-4 py-2 rounded-xl bg-[#1ec3a6] hover:bg-[#1aa893] text-white font-semibold"
      @click="goBuild"
    >
      Voir / finaliser mon build
    </button>
  </div>
</template>
