import { defineStore } from 'pinia'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [] // { type: 'build'|'component', id: number, qty: number }
  }),
  getters: {
    count: (s) => s.items.reduce((a, i) => a + i.qty, 0),
  },
  actions: {
    add(item) {
      const key = `${item.type}:${item.id}`
      const found = this.items.find(i => `${i.type}:${i.id}` === key)
      if (found) found.qty += item.qty ?? 1
      else this.items.push({ type: item.type, id: item.id, qty: item.qty ?? 1 })
    },
    remove(item) {
      const key = `${item.type}:${item.id}`
      this.items = this.items.filter(i => `${i.type}:${i.id}` !== key)
    },
    clear() { this.items = [] }
  }
})
