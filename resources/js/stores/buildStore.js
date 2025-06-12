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
    }
  }),
  actions: {
    addComponent(type, component) {
      this.build[type] = component
    },
    removeComponent(type) {
      this.build[type] = null
    }
  },
  persist: true
})
