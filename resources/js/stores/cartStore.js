/**
 * Store Pinia pour gérer le panier (cart).
 *
 * - Contient à la fois des builds et des composants individuels
 * - Fournit des actions pour ajouter, retirer et vider
 * - Inclut la persistance via localStorage (reste après un reload ou navigation)
 */

import { defineStore } from 'pinia'

export const useCartStore = defineStore('cart', {
  // ---------------- État ----------------
  state: () => ({
    /**
     * Liste des items du panier
     * Chaque item : { type: 'build'|'component', id: number, qty: number }
     */
    items: [],
  }),

  // ---------------- Getters ----------------
  getters: {
    /**
     * Nombre total d’items (somme des quantités)
     */
    count: (state) => state.items.reduce((acc, item) => acc + item.qty, 0),
  },

  // ---------------- Actions ----------------
  actions: {
    /**
     * Ajoute un item au panier.
     * - Si l’item existe déjà (même type + id), on incrémente la quantité.
     * - Sinon on insère un nouvel objet.
     */
    add(item) {
      const key = `${item.type}:${item.id}`
      const found = this.items.find(i => `${i.type}:${i.id}` === key)
      if (found) {
        found.qty += item.qty ?? 1
      } else {
        this.items.push({ type: item.type, id: item.id, qty: item.qty ?? 1 })
      }
    },

    /**
     * Supprime un item du panier (par type + id).
     */
    remove(item) {
      const key = `${item.type}:${item.id}`
      this.items = this.items.filter(i => `${i.type}:${i.id}` !== key)
    },

    /**
     * Vide complètement le panier.
     * À n’appeler que lorsque l’utilisateur le souhaite,
     * pas après un SAVE automatique (sinon perte des données).
     */
    clear() {
      this.items = []
    },
  },

  // ---------------- Persistance ----------------
  /**
   * Persistance locale via localStorage :
   * - Les items restent même après un refresh ou navigation
   * - La clé est "pcbuilder_cart"
   */
  persist: {
    key: 'pcbuilder_cart',
    storage: localStorage,
    paths: ['items'], // on ne persiste que les items
  },
})
