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

/* ----------------------- Résolution clé de slot ----------------------- */
// mini slug sans dépendance
function slugify(s = '') {
  return String(s).toLowerCase().trim()
    .replace(/\s+/g, '_').replace(/-+/g, '_').replace(/[^\w_]/g, '')
}
// fallback si le store n’a pas resolveTypeFromComponent
function fallbackResolveType(c) {
  const raw =
    c?.component_type?.name ??
    c?.type?.name ??
    c?.type ??
    ''
  const s = slugify(raw)
  // normalise quelques cas
  const map = { 'case': 'case_model', 'boitier': 'case_model', 'stockage': 'storage' }
  return map[s] || s
}

const key = computed(() =>
  build.resolveTypeFromComponent?.(props.component) || fallbackResolveType(props.component)
)

const selected = computed(() => key.value ? build.build?.[key.value] : null)

const dejaAjoute = computed(() => {
  const cur = selected.value
  const id = props.component?.id
  return !!(cur && id && (cur.id === id || cur.component_id === id))
})

const incompatible = computed(() => {
  if (!key.value || !props.component?.id) return false
  // si la méthode n’existe pas on ne bloque pas
  return build.isCompatible ? !build.isCompatible(key.value, props.component.id) : false
})

/* ---------------------------- Actions ---------------------------- */
async function add() {
  console.debug('[AddToBuild] click', {
    key: key.value,
    compId: props.component?.id,
    selected: selected.value,
    dejaAjoute: dejaAjoute.value,
    incompatible: incompatible.value,
  })

  if (!key.value) {
    alert("Type inconnu, impossible d'ajouter au build.")
    return
  }
  if (incompatible.value) return
  if (selected.value && !dejaAjoute.value) {
    confirmReplace.value = true
    return
  }
  doAdd()
}

function doAdd() {
  busy.value = true
  try {
    // 1) API “officielle” si dispo
    if (typeof build.addFromComponent === 'function') {
      build.addFromComponent(props.component)
    }
    // 2) Sinon addComponent(key, payload) si dispo
    else if (typeof build.addComponent === 'function') {
      const c = props.component
      build.addComponent(key.value, {
        id: c.id,
        component_id: c.id,
        name: c.name,
        price: c.price,
        img_url: c.img_url,
      })
    }
    // 3) Fallback direct sur l’état
    else if (build.build && Object.prototype.hasOwnProperty.call(build.build, key.value)) {
      build.build[key.value] = {
        id: props.component.id,
        name: props.component.name,
        price: props.component.price,
        img_url: props.component.img_url,
      }
    } else {
      console.error('[AddToBuild] aucune méthode pour ajouter le composant')
      alert("Impossible d'ajouter: store non initialisé.")
      return
    }

    // (optionnel) revalidation
    build.validateBuild?.()
    console.debug('[AddToBuild] ajouté OK →', { key: key.value, id: props.component.id })
  } catch (e) {
    console.error('[AddToBuild] erreur', e)
    alert("Une erreur est survenue lors de l'ajout au build.")
  } finally {
    busy.value = false
    confirmReplace.value = false
  }
}

/* ---------------------------- UI ---------------------------- */
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

    <!-- Modal de remplacement -->
    <div v-if="confirmReplace" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl">
        <h3 class="text-lg font-bold mb-2">Remplacer le {{ key }}</h3>
        <p class="text-sm text-slate-700 mb-4">
          Un {{ key }} est déjà présent dans votre build.
          Voulez-vous le remplacer par <b>{{ component.name }}</b> ?
        </p>
        <div class="flex justify-end gap-2">
          <button class="px-4 py-2 rounded-lg bg-slate-200" @click="confirmReplace=false">Annuler</button>
          <button class="px-4 py-2 rounded-lg bg-[#1ec3a6] text-white" @click="doAdd">Remplacer</button>
        </div>
      </div>
    </div>
  </div>
</template>
