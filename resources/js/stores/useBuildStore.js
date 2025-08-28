import { defineStore } from 'pinia'
import axios from 'axios'

/**
 * Store ÉDITEUR de build (recette). Ne gère pas le panier/checkout.
 * - Sélectionne les pièces
 * - Calcule le prix
 * - Valide la compatibilité (API)
 * - Produit un payload propre pour sauvegarder le build
 *
 * Règle de validité par défaut: CPU + Motherboard + RAM + Storage + PSU
 * (GPU/Cooler/Case sont optionnels ici)
 */
export const useBuildStore = defineStore('build', {
  state: () => ({
    // État du build (clés internes normalisées)
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

    // Métadonnées / texte
    buildName: '',
    buildDescription: '',
    buildImgUrl: '',

    // Validation & compatibilité
    errors: [],
    warnings: [],
    compatibility: {}, // ex: { motherboard: [1, 2, 3], ram: [5, 6] }
    validating: false,
    lastRequest: null,

    // Règles (modifiable si besoin)
    requiredKeys: ['cpu', 'motherboard', 'ram', 'storage', 'psu'],
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
    },

    selectedComponents(state) {
      // Renvoie un tableau [{ key, id, data }]
      const out = []
      for (const [key, comp] of Object.entries(state.build)) {
        if (comp && (comp.id ?? comp.component_id)) {
          out.push({ key, id: comp.id ?? comp.component_id, data: comp })
        }
      }
      return out
    },

    selectedIds(state) {
      return Object.values(state.build)
        .filter(Boolean)
        .map(c => c.id ?? c.component_id)
    }
  },

  actions: {
    /** Normalise tout un tas d'alias/labels vers nos clés internes */
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

    /** Inverse soft: depuis une clé interne -> label type pour l'API de persistance */
    presentType(key) {
      // Ici on renvoie simplement la clé normalisée ; à adapter si tu veux des labels
      return key
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

    setInfo({ name, description, imgUrl } = {}) {
      if (typeof name === 'string') this.buildName = name
      if (typeof description === 'string') this.buildDescription = description
      if (typeof imgUrl === 'string') this.buildImgUrl = imgUrl
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
      // Utilitaire: recharger le store à partir d'un objet build existant
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
    },

    /**
     * Validation API avec compatibilité proactive
     * (appel throttle/débounce 300ms)
     */
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
    },

    /**
     * Retourne la liste des clés manquantes selon la règle requiredKeys
     */
    missingRequired() {
      const missing = []
      for (const key of this.requiredKeys) {
        const comp = this.build[key]
        const hasId = !!(comp && (comp.id ?? comp.component_id))
        if (!hasId) missing.push(key)
      }
      return missing
    },

    /**
     * Règle de validité globale pour “Sauvegarder & Commander”
     * (Par défaut: CPU + Motherboard + RAM + Storage + PSU)
     */
    isValid() {
      return this.missingRequired().length === 0
    },

    /**
     * Construit le payload pour POST /api/builds (ou ton BuildController@store JSON)
     * Format proposé:
     * {
     *   name, description, img_url, price, components: [{ id, type }]
     * }
     */
    toPayload() {
      const components = []
      for (const [key, comp] of Object.entries(this.build)) {
        if (comp && (comp.id ?? comp.component_id)) {
          components.push({
            id: comp.id ?? comp.component_id,
            type: this.presentType(key),
          })
        }
      }

      // Nom fallback si vide
      const safeName = (this.buildName && this.buildName.trim().length)
        ? this.buildName.trim()
        : `Build ${new Date().toLocaleString()}`

      // Prix total client (optionnel si tu veux le recalculer serveur)
      const total = Number.isFinite(this.totalPrice) ? Number(this.totalPrice) : 0

      return {
        name: safeName,
        description: this.buildDescription || null,
        img_url: this.buildImgUrl || null,
        price: Number(total.toFixed(2)),
        components
      }
    }
  },

  // Persistance localStorage (nécessite pinia-plugin-persistedstate si utilisé)
  persist: {
    key: 'pcbuilder-build',
    paths: [
      'build', 'buildName', 'buildDescription', 'buildImgUrl',
      'errors', 'warnings', 'compatibility', 'requiredKeys'
    ],
    storage: typeof window !== 'undefined' ? window.localStorage : undefined,
  }
})
