import { defineStore } from 'pinia'
import { reactive } from 'vue'

export const useBuildStore = defineStore('build', () => {
  // Un objet réactif pour stocker le build en construction
  const build = reactive({
    cpu: null,
    gpu: null,
    ram: null,
    motherboard: null,
    storage: null,
    psu: null,
    cooler: null,
    case_model: null,
  })

  // Ajoute ou remplace un composant dans le build
  function addComponent(type, component) {
    build[type] = component
  }

  // Supprime un composant du build
  function removeComponent(type) {
    build[type] = null
  }

  return { build, addComponent, removeComponent }
})
