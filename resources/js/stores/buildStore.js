import { defineStore } from 'pinia'
import axios from 'axios'

export const useBuildStore = defineStore('build', {
  state: () => ({
    // État du build (tous uniques côté UI ; "storage" peut devenir multi plus tard)
    build: {
      cpu: null,
      gpu: null,
      ram: null,
      motherboard: null,
      storage: null,
      psu: null,
      cooler: null,
      case_model: null, // boîtier
    },
    buildName: '',
    buildDescription: '',
    buildImgUrl: '',

    // Validation & compatibilité
    errors: [],
    warnings: [],
    compatibility: {}, // ex: { motherboard: [1, 2, 3], ram: [5, 6] }
    validating: false,
    lastRequest: null,
  }),

  getters: {
    totalPrice(state) {
      return Object.values(state.build).reduce((sum, c) => {
        const p = c && c.price != null ? Number(c.price) : 0
        return sum + (isNaN(p) ? 0 : p)
      }, 0)
    },
    hasAnyComponent(state) {
      return Object.values(state.build).some(v => !!v)
    },
    isCompatible: (state) => (type, id) => {
      if (!type) return true
      const list = state.compatibility?.[type]
      if (!Array.isArray(list) || list.length === 0) return true
      const compId = Number(id)
      return list.includes(compId)
    },
  },

  actions: {
    // Normalise un libellé de type vers une clé du state.build
    normalizeType(raw) {
      if (!raw) return ''
      let k = ''
      if (typeof raw === 'string') k = raw
      else if (typeof raw === 'object' && (raw.name || raw.slug)) k = raw.name || raw.slug
      k = (k || '').trim().toLowerCase()

      switch (k) {
        case 'cpu':
        case 'processeur':
          return 'cpu'

        case 'gpu':
        case 'carte graphique':
          return 'gpu'

        case 'ram':
        case 'mémoire':
        case 'memoire':
          return 'ram'

        case 'motherboard':
        case 'carte mère':
        case 'carte mere':
          return 'motherboard'

        case 'storage':
        case 'stockage':
        case 'ssd':
        case 'hdd':
          return 'storage'

        case 'psu':
        case 'power supply':
        case 'alimentation':
          return 'psu'

        case 'cooler':
        case 'ventirad':
          return 'cooler'

        // Boîtier : accepter plusieurs variantes
        case 'case':
        case 'case model':
        case 'case_model':
        case 'case-model':
        case 'chassis':
        case 'boîtier':
        case 'boitier':
          return 'case_model'

        default:
          return ''
      }
    },

    // Déduit le type depuis divers formats d'objet (name/slug)
    resolveTypeFromComponent(component) {
      const rawType =
        component?.type?.slug ??
        component?.component_type?.slug ??
        component?.type?.name ??
        component?.type ??
        component?.component_type?.name ??
        component?.component_type ??
        ''
      return this.normalizeType(rawType)
    },

    // API haut-niveau : ajoute un composant “brut”
    addFromComponent(component) {
      const key = this.resolveTypeFromComponent(component)
      if (!key || !Object.prototype.hasOwnProperty.call(this.build, key)) {
        throw new Error('Unknown component type')
      }
      this.build[key] = component
    },

    // API bas-niveau : ajoute avec un type fourni (string ou objet)
    addComponent(type, component) {
      const key = this.normalizeType(type)
      if (!key || !Object.prototype.hasOwnProperty.call(this.build, key)) return
      this.build[key] = component
    },

    removeComponent(type) {
      const key = this.normalizeType(type)
      if (!key || !Object.prototype.hasOwnProperty.call(this.build, key)) return
      this.build[key] = null
    },

    reset() {
      Object.keys(this.build).forEach(k => { this.build[k] = null })
      this.buildName = ''
      this.buildDescription = ''
      this.buildImgUrl = ''
      this.errors = []
      this.warnings = []
      this.compatibility = {}
    },

    fillFromBuild(build) {
      this.reset()
      if (build?.name) this.buildName = `${build.name}(copie)`
      if (build?.description) this.buildDescription = build.description
      if (build?.img_url) this.buildImgUrl = build.img_url

      const comps = Array.isArray(build?.components) ? build.components : []
      for (const component of comps) {
        const key = this.resolveTypeFromComponent(component)
        if (key && Object.prototype.hasOwnProperty.call(this.build, key)) {
          this.build[key] = component
        }
      }
    },

    // Liste des IDs normalisés pour l'API (préférence component_id)
    getComponentIds() {
      return Object.values(this.build)
        .filter(Boolean)
        .map(c => c.component_id ?? c.id)
        .map(v => Number(v))
        .filter(v => Number.isFinite(v))
    },

    // Validation distante (debounced) + compatibilité proactive
    validateBuild() {
      const ids = this.getComponentIds()

      if (this.lastRequest) clearTimeout(this.lastRequest)

      this.lastRequest = setTimeout(async () => {
        this.validating = true
        this.errors = []
        this.warnings = []
        try {
          const res = await axios.post('/api/builds/validate-temp', {
            component_ids: ids
          })

          this.errors = res.data?.errors || []
          this.warnings = res.data?.warnings || []

          // Back possible: "compatible" ou "compatible_ids"
          const rawCompat = res.data?.compatible || res.data?.compatible_ids || {}
          const numericCompat = {}

          Object.keys(rawCompat).forEach(k => {
            const arr = Array.isArray(rawCompat[k]) ? rawCompat[k] : []
            numericCompat[k] = arr
              .map(v => Number(v))
              .filter(v => Number.isFinite(v))
          })

          this.compatibility = numericCompat
        } catch (e) {
          this.errors = ['Erreur serveur lors de la validation.']
          this.compatibility = {}
        } finally {
          this.validating = false
        }
      }, 300)
    },

    // Éléments requis (simple baseline, à adapter si besoin)
    missingRequired() {
      const required = ['cpu', 'motherboard', 'ram', 'storage', 'psu', 'case_model']
      const missing = []
      for (const k of required) {
        if (!this.build[k]) missing.push(
          k === 'case_model' ? 'Boîtier' :
          k === 'psu' ? 'Alimentation' :
          k.charAt(0).toUpperCase() + k.slice(1)
        )
      }
      return missing
    },

    isValid() {
      return this.missingRequired().length === 0 && this.errors.length === 0
    },
  },

  persist: {
    key: 'pcbuilder-build',
    paths: [
      'build',
      'buildName',
      'buildDescription',
      'buildImgUrl',
      'errors',
      'warnings',
      'compatibility',
    ],
    storage: typeof window !== 'undefined' ? window.localStorage : undefined,
  },
})
