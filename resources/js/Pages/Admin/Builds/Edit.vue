<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import GoBackButton from '@/Components/GoBackButton.vue'

const props = defineProps({
  build: { type: Object, required: true },
  // paginate(15) -> { data, links, ... }
  allComponents: { type: Object, required: true },
})

/* ------------------ Helpers ------------------ */
function formatEUR(n) {
  const val = Number.isFinite(n) ? n : (parseFloat(n ?? NaN) || 0)
  try { return new Intl.NumberFormat('fr-BE', { style: 'currency', currency: 'EUR' }).format(val) }
  catch { return `${val.toFixed(2)} €` }
}

/* ------------------ Données form ------------------ */
// Composants sélectionnés (on garde la quantité depuis le pivot si présent)
const selectedComponents = ref(
  Array.isArray(props.build?.components)
    ? props.build.components.map(c => ({
        component_id: c.id,
        name: c.name,
        quantity: typeof c.pivot?.quantity === 'number' ? c.pivot.quantity : 1,
      }))
    : []
)

const form = useForm({
  name: props.build?.name ?? '',
  description: props.build?.description ?? '',
  price: props.build?.price ?? '',
  img_url: props.build?.img_url ?? '',
  // carousel
  is_featured: Boolean(props.build?.is_featured ?? false),
  featured_rank: props.build?.featured_rank ?? null,
  // composants
  components: selectedComponents.value, // sera nettoyé avant submit
})

/* ------------------ Catalogue fusionné ------------------ */
// Page courante du catalogue
const pageCatalog = computed(() =>
  Array.isArray(props.allComponents) ? props.allComponents : (props.allComponents?.data ?? [])
)

// Composants du build (avec brand/type/prix) — pour créer les options manquantes
const selectedFull = computed(() =>
  Array.isArray(props.build?.components)
    ? props.build.components.map(c => ({
        id: c.id,
        name: c.name,
        brand: c.brand ?? null,
        type: c.type ?? null,
        price: c.price ?? 0,
      }))
    : []
)

// Union unique (par id) : sélection actuelle + page catalogue
const catalogWithSelected = computed(() => {
  const map = new Map()
  for (const c of selectedFull.value) map.set(c.id, c)
  for (const c of pageCatalog.value) map.set(c.id, c)
  return Array.from(map.values())
})

// Accès rapide au prix unitaire par id (pour afficher le total live)
const priceById = computed(() => {
  const m = new Map()
  for (const c of catalogWithSelected.value) m.set(c.id, Number(c.price ?? 0))
  return m
})

/* ------------------ Total live ------------------ */
const liveTotal = computed(() => {
  return selectedComponents.value.reduce((sum, row) => {
    const unit = priceById.value.get(Number(row.component_id)) ?? 0
    const qty  = Math.max(1, parseInt(row.quantity ?? 1, 10) || 1)
    return sum + unit * qty
  }, 0)
})

/* ------------------ Actions UI ------------------ */
function addComponent () {
  form.components.push({ component_id: null, name: '', quantity: 1 })
}
function removeComponent (idx) {
  form.components.splice(idx, 1)
}

// Nettoyage & submit
function submit () {
  const cleaned = form.components
    .filter(c => c.component_id != null && c.component_id !== '')
    .map(c => ({
      component_id: Number(c.component_id),
      quantity: Math.max(1, parseInt(c.quantity ?? 1, 10) || 1),
    }))

  form.components = cleaned
  form.put(route('admin.builds.update', props.build.id), { preserveScroll: true })
}

// Pagination du catalogue
function visitLink (url) {
  if (!url) return
  router.visit(url, { preserveScroll: true, preserveState: true })
}
</script>

<template>
  <div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl shadow-lg border">
    <GoBackButton class="mb-4" />
    <h1 class="text-2xl font-bold text-darknavy mb-6">
      Éditer le build #{{ build?.id }}
    </h1>

    <form @submit.prevent="submit" class="space-y-6">
      <!-- Meta -->
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block font-semibold mb-1">Nom</label>
          <input v-model="form.name" required class="w-full border rounded-xl px-3 py-2" />
          <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
        </div>

        <div>
          <label class="block font-semibold mb-1">Total estimé (live)</label>
          <input :value="formatEUR(liveTotal)" class="w-full border rounded-xl px-3 py-2 bg-gray-50" disabled />
        </div>
      </div>

      <div>
        <label class="block font-semibold mb-1">Description</label>
        <textarea v-model="form.description" class="w-full border rounded-xl px-3 py-2" />
        <p v-if="form.errors.description" class="text-sm text-red-600 mt-1">{{ form.errors.description }}</p>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block font-semibold mb-1">Prix (optionnel)</label>
          <input v-model="form.price" type="number" step="0.01" class="w-full border rounded-xl px-3 py-2" />
          <p v-if="form.errors.price" class="text-sm text-red-600 mt-1">{{ form.errors.price }}</p>
        </div>
        <div>
          <label class="block font-semibold mb-1">Image (URL)</label>
          <input v-model="form.img_url" class="w-full border rounded-xl px-3 py-2" />
          <p v-if="form.errors.img_url" class="text-sm text-red-600 mt-1">{{ form.errors.img_url }}</p>
        </div>
      </div>

      <!-- Carousel (mise en avant) -->
      <div class="flex items-center gap-3">
        <label class="inline-flex items-center gap-2">
          <input type="checkbox" v-model="form.is_featured" />
          <span>Mettre en avant (carousel)</span>
        </label>
        <input
          v-model.number="form.featured_rank"
          type="number"
          min="1" max="3"
          class="border rounded-xl px-3 py-2 w-24"
          placeholder="Rang (1-3)"
          :disabled="!form.is_featured"
        />
      </div>

      <!-- Composants -->
      <div class="space-y-3">
        <label class="block font-semibold">Composants du build</label>

        <div
          v-for="(c, idx) in form.components"
          :key="`row-${idx}-${c.component_id ?? 'new'}`"
          class="flex items-center gap-2"
        >
          <select
            v-model.number="c.component_id"
            required
            class="border rounded-xl px-3 py-2 w-2/3"
          >
            <option :value="null">Sélectionner un composant</option>
            <option
              v-for="comp in catalogWithSelected"
              :key="comp.id"
              :value="comp.id"
            >
              {{ comp.name }}
              <template v-if="comp.brand?.name"> ({{ comp.brand.name }}</template>
              <template v-if="comp.type?.name"> - {{ comp.type.name }}</template>
              <template v-if="comp.brand?.name">)</template>
            </option>
          </select>

          <input
            v-model.number="c.quantity"
            type="number"
            min="1"
            class="border rounded-xl px-3 py-2 w-20 text-center"
          />

          <!-- Prix unitaire affiché -->
          <div class="w-28 text-right text-sm text-gray-600">
            {{
              formatEUR(
                priceById.get(Number(c.component_id)) ?? 0
              )
            }}
          </div>

          <button
            type="button"
            @click="removeComponent(idx)"
            class="text-red-600 px-2 font-bold"
            aria-label="Retirer"
            title="Retirer"
          >×</button>
        </div>

        <button
          type="button"
          @click="addComponent"
          class="bg-primary hover:bg-cyan text-white px-4 py-1 rounded-xl shadow text-sm"
        >
          Ajouter un composant
        </button>

        <!-- Erreurs de validation -->
        <div class="text-sm text-red-600" v-if="form.errors['components']">
          {{ form.errors['components'] }}
        </div>
        <div class="text-sm text-red-600" v-if="form.errors['components.*.component_id']">
          {{ form.errors['components.*.component_id'] }}
        </div>
        <div class="text-sm text-red-600" v-if="form.errors['components.*.quantity']">
          {{ form.errors['components.*.quantity'] }}
        </div>
      </div>

      <!-- Catalogue (pagination) -->
      <div class="border rounded-xl p-4">
        <div class="font-semibold mb-2">Catalogue de composants</div>
        <ul class="divide-y">
          <li v-for="comp in pageCatalog" :key="comp.id" class="py-2 flex items-center gap-3">
            <div class="min-w-0">
              <div class="font-medium truncate">
                {{ comp.name }}
                <span v-if="comp.brand?.name" class="text-xs text-gray-500">
                  ({{ comp.brand.name }})
                </span>
              </div>
              <div class="text-xs text-gray-500">
                {{ comp.type?.name ?? '—' }} • {{ formatEUR(comp.price ?? 0) }}
              </div>
            </div>
            <button
              type="button"
              class="ml-auto px-3 py-1 rounded border"
              @click="form.components.push({ component_id: comp.id, name: comp.name, quantity: 1 })"
            >
              Ajouter
            </button>
          </li>
        </ul>

        <div
          v-if="props.allComponents?.links"
          class="flex flex-wrap gap-2 justify-center pt-3"
        >
          <button
            v-for="(link, i) in props.allComponents.links"
            :key="i"
            class="px-3 py-1 rounded border text-sm"
            :class="[{ 'bg-darknavy text-white': link.active }]"
            :disabled="!link.url"
            v-html="link.label"
            @click.prevent="visitLink(link.url)"
          />
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-3">
        <button
          type="button"
          class="bg-lightgray text-darknavy px-4 py-2 rounded-xl border"
          @click="$inertia.visit(route('admin.builds.index'))"
        >
          Annuler
        </button>
        <button
          type="submit"
          class="bg-primary hover:bg-cyan text-white px-6 py-2 rounded-xl shadow font-semibold disabled:opacity-50"
          :disabled="form.processing"
        >
          {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
        </button>
      </div>
    </form>
  </div>
</template>
