<script setup>
import { ref, computed } from 'vue'
import { useBuildStore } from '@/stores/buildStore'

const props = defineProps({
  component: { type: Object, required: true },
  size: { type: String, default: 'md' }, // sm|md|lg
  showPrice: { type: Boolean, default: false },
})

const build = useBuildStore()
const busy = ref(false)
const confirmReplace = ref(false)

const key = computed(() => build.resolveTypeFromComponent(props.component))
const selected = computed(() => key.value ? build.build[key.value] : null)

const dejaAjoute = computed(() => {
  const cur = selected.value
  const id = props.component?.id
  return !!(cur && id && (cur.id === id || cur.component_id === id))
})

const incompatible = computed(() => {
  // On utilise la compatibilité proactive si dispo
  return key.value && !build.isCompatible?.(key.value, props.component?.id)
})

async function add() {
  if (!key.value) {
    alert("Type inconnu, impossible d'ajouter au build.")
    return
  }
  // Remplacement si un autre composant du même type est déjà présent
  if (selected.value && !dejaAjoute.value) {
    confirmReplace.value = true
    return
  }
  doAdd()
}

function doAdd() {
  busy.value = true
  try {
    build.addFromComponent(props.component)
    build.validateBuild?.()
  } finally {
    busy.value = false
    confirmReplace.value = false
  }
}

const sizeClass = computed(() => ({
  sm: 'px-3 py-1 text-xs',
  md: 'px-4 py-2 text-sm',
  lg: 'px-5 py-3 text-base',
}[props.size] ?? 'px-4 py-2 text-sm'))
</script>

<template>
  <div class="inline-flex items-center gap-2">
    <button
      :disabled="busy || dejaAjoute || incompatible"
      @click="add"
      class="rounded-xl font-semibold shadow
             bg-[#1ec3a6] hover:bg-[#1aa893] text-white
             disabled:opacity-60 disabled:cursor-not-allowed
             transition"
      :class="sizeClass"
      :title="incompatible ? 'Incompatible avec votre sélection actuelle' : ''"
    >
      <span v-if="dejaAjoute">Déjà dans le build</span>
      <span v-else-if="busy">Ajout…</span>
      <span v-else>Ajouter au build</span>
      <span v-if="showPrice && component?.price" class="ml-2 opacity-90">
        ({{ Number(component.price).toFixed(2) }} €)
      </span>
    </button>

    <!-- Modal de remplacement minimaliste -->
    <div v-if="confirmReplace"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl">
        <h3 class="text-lg font-bold mb-2">Remplacer le {{ key }}</h3>
        <p class="text-sm text-slate-700 mb-4">
          Un {{ key }} est déjà présent dans votre build.
          Voulez-vous le remplacer par <b>{{ component.name }}</b> ?
        </p>
        <div class="flex justify-end gap-2">
          <button class="px-4 py-2 rounded-lg bg-slate-200"
                  @click="confirmReplace=false">Annuler</button>
          <button class="px-4 py-2 rounded-lg bg-[#1ec3a6] text-white"
                  @click="doAdd">Remplacer</button>
        </div>
      </div>
    </div>
  </div>
</template>
