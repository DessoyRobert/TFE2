import { defineStore } from 'pinia'

export const useBuildStore = defineStore('build', {
  state: () => ({
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
  }),

  getters: {
    // Somme robuste (tolère number|string|null)
    totalPrice(state) {
      return Object.values(state.build).reduce((sum, c) => {
        const p = c && c.price != null ? Number(c.price) : 0
        return sum + (isNaN(p) ? 0 : p)
      }, 0)
    },
    hasAnyComponent(state) {
      return Object.values(state.build).some(v => !!v)
    },
  },

  actions: {
    /**
     * Normalise un libellé de type venant du back vers nos clés de store.
     * "carte mère"/"motherboard" -> "motherboard"
     * "case"/"case model"/"boîtier" -> "case_model"
     */
    normalizeType(raw) {
      if (!raw) return ''
      let k = ''
      if (typeof raw === 'string') {
        k = raw
      } else if (typeof raw === 'object' && raw.name) {
        k = raw.name
      }
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
        case 'case':
        case 'case model':
        case 'boîtier':
        case 'boitier':
          return 'case_model'
        default:
          return ''
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
      Object.keys(this.build).forEach((k) => {
        this.build[k] = null
      })
      this.buildName = ''
      this.buildDescription = ''
      this.buildImgUrl = ''
    },

    /**
     * Remplit le store depuis un objet "build" (venant de la liste/détail).
     * Tolère: component.type.name, component.component_type.name, ou string.
     */
    fillFromBuild(build) {
      this.reset()

      if (build && build.name) this.buildName = `${build.name}(copie)`
      if (build && build.description) this.buildDescription = build.description
      if (build && build.img_url) this.buildImgUrl = build.img_url

      const comps = Array.isArray(build?.components) ? build.components : []
      for (const component of comps) {
        // on essaye type, component_type, ou à défaut component.component_type
        const rawType = component?.type 
                    ?? component?.component_type 
                    ?? component?.component?.component_type 
                    ?? ''
        const key = this.normalizeType(rawType)
        if (key && Object.prototype.hasOwnProperty.call(this.build, key)) {
          this.build[key] = component
        }
      }
    }
,
  },

  // Persistance ciblée (nécessite le plugin pinia-plugin-persistedstate)
  persist: {
    key: 'pcbuilder-build',
    paths: ['build', 'buildName', 'buildDescription', 'buildImgUrl'],
    // sur SSR, window est absent → garde-fou
    storage: typeof window !== 'undefined' ? window.localStorage : undefined,
  },
})
