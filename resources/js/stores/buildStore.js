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
  actions: {
    addComponent(type, component) {
      this.build[type] = component
    },
    removeComponent(type) {
      this.build[type] = null
    },
    reset() {
      for (const key in this.build) {
        this.build[key] = null
      }
      this.buildName = ''
      this.buildDescription = ''
      this.buildImgUrl = ''
    },
    fillFromBuild(build) {
      this.reset()
      if (build.name) this.buildName = build.name+'(copie)'
      if (build.description) this.buildDescription = build.description
      if (build.img_url) this.buildImgUrl = build.img_url
      if (build.components) {
        for (const component of build.components) {
          const type = component?.type?.name?.toLowerCase() ||
                       component?.component_type?.name?.toLowerCase()
          if (type && this.build.hasOwnProperty(type)) {
            this.build[type] = component
          }
        }
      }
    }
  },
  persist: true
})
