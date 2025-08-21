import { defineStore } from 'pinia'
import axios from 'axios'

export const useBuildStore = defineStore('build', {
  state: () => ({
    // État du build
    build: {
      cpu: null,
      gpu: null,
      ram: null,
      motherboard: null,
      storage: null,
      psu: null,
      cooler: null,
      case_model: null,
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
      if (!state.compatibility[type]) return true
      return state.compatibility[type].includes(id)
    }
  },

  actions: {
    normalizeType(raw) {
      if (!raw) return ''
      let k = ''
      if (typeof raw === 'string') k = raw
      else if (typeof raw === 'object' && raw.name) k = raw.name
      k = (k || '').trim().toLowerCase()

      switch (k) {
        case 'cpu': case 'processeur': return 'cpu'
        case 'gpu': case 'carte graphique': return 'gpu'
        case 'ram': case 'mémoire': case 'memoire': return 'ram'
        case 'motherboard': case 'carte mère': case 'carte mere': return 'motherboard'
        case 'storage': case 'ssd': case 'hdd': return 'storage'
        case 'psu': case 'power supply': case 'alimentation': return 'psu'
        case 'cooler': case 'ventirad': return 'cooler'
        case 'case': case 'case model': case 'boîtier': case 'boitier': return 'case_model'
        default: return ''
      }
    },

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
      console.log('=== fillFromBuild appelé avec ===', build)
      this.reset()
      if (build?.name) this.buildName = `${build.name}(copie)`
      if (build?.description) this.buildDescription = build.description
      if (build?.img_url) this.buildImgUrl = build.img_url

      const comps = Array.isArray(build?.components) ? build.components : []
      for (const component of comps) {
        const rawType = component?.type 
                      ?? component?.component_type 
                      ?? component?.component?.component_type 
                      ?? ''
        const key = this.normalizeType(rawType)
        if (key && Object.prototype.hasOwnProperty.call(this.build, key)) {
          this.build[key] = component
        }
      }
      console.log('=== Store après fillFromBuild ===', this.build)
    },

    // Validation API avec compatibilité proactive
    validateBuild() {
      const ids = Object.values(this.build)
        .filter(Boolean)
        .map(c => c.id ?? c.component_id)

      if (this.lastRequest) clearTimeout(this.lastRequest)

      this.lastRequest = setTimeout(async () => {
        this.validating = true
        this.errors = []
        this.warnings = []
        try {
          const res = await axios.post('/api/builds/validate-temp', {
            component_ids: ids
          })
          this.errors = res.data.errors || []
          this.warnings = res.data.warnings || []
          this.compatibility = res.data.compatible || {}
        } catch (e) {
          this.errors = ['Erreur serveur lors de la validation.']
          this.compatibility = {}
        } finally {
          this.validating = false
        }
      }, 300)
    }
  },

  persist: {
    key: 'pcbuilder-build',
    paths: [
      'build', 'buildName', 'buildDescription', 'buildImgUrl',
      'errors', 'warnings', 'compatibility'
    ],
    storage: typeof window !== 'undefined' ? window.localStorage : undefined,
  }
})
