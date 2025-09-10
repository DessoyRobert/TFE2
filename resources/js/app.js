import '../css/app.css'
import './bootstrap'

import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h } from 'vue'
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'

// Importation des layouts globaux
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: async (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue')
    const page = await pages[`./Pages/${name}.vue`]()
    const component = page.default

    // Application du layout selon une convention ou fallback
    if (!component.layout) {
      if (name.startsWith('Auth/')) {
        component.layout = GuestLayout
      } else if (name.startsWith('Admin/')) {
        component.layout = AdminLayout
      } else if (name.startsWith('Dashboard')) {
        component.layout = AuthenticatedLayout
      } else {
        component.layout = DefaultLayout
      }
    }

    return component
  },
  setup({ el, App, props, plugin }) {
    // === PATCH PERSISTENCE PINIA ===
    const pinia = createPinia()
    pinia.use(piniaPluginPersistedstate)
    // ==============================

    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(pinia) // <= instance pinia avec la persistance activée
      .use(ZiggyVue)
      .mount(el)
  },
  progress: {
    color: '#4B5563',
  },
})
