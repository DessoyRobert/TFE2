<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed, ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { useCartStore } from '@/stores/cartStore'

/* Stores / props */
const cart = useCartStore()
const count = computed(() => cart.count)

const page = usePage()
const user = computed(() => page.props.auth.user)
const isAdmin = computed(() => user.value?.is_admin)

/* Actions */
function logout() { router.post(route('logout')) }

/* UI state */
const open = ref(false)
const scrolled = ref(false)

/* helpers */
function toggle() { open.value = !open.value }
function close() { open.value = false }
function onKeydown(e) { if (e.key === 'Escape') close() }
function onScroll() { scrolled.value = window.scrollY > 6 }
function onResize() { if (window.matchMedia('(min-width: 768px)').matches) close() }

/* active link */
const currentPath = computed(() => page.url || '')
function isActive(path) {
  if (path === '/') return currentPath.value === '/'
  return currentPath.value.startsWith(path)
}

/* close on navigation */
watch(() => page.url, () => close())

onMounted(() => {
  window.addEventListener('keydown', onKeydown)
  window.addEventListener('scroll', onScroll, { passive: true })
  window.addEventListener('resize', onResize)
  onScroll()
})
onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeydown)
  window.removeEventListener('scroll', onScroll)
  window.removeEventListener('resize', onResize)
})
</script>

<template>
  <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:bg-white focus:text-black focus:px-3 focus:py-1 focus:rounded">
    Aller au contenu
  </a>

  <header
    :class="[
      'sticky top-0 z-50 bg-darknavy backdrop-blur rounded-b-2xl transition-shadow',
      scrolled ? 'shadow-lg ring-1 ring-black/10' : 'shadow-md'
    ]"
    role="banner"
  >
    <div class="mx-auto max-w-8xl px-4 sm:px-6">
      <div class="flex items-center justify-between py-5">
        <!-- Logo + titre -->
        <div class="flex items-center gap-3 min-w-0">
          <a
            href="https://jarvistech.be/"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center gap-2 hover:opacity-80 transition-opacity"
          >
            <img
              src="https://res.cloudinary.com/djllwl8c0/image/upload/f_auto,q_auto,w_192,h_48,c_fit/Logo-JarvisTech-PNG-normalsansfond_pgxlrj.png"
              alt="Logo JarvisTech"
              width="256" height="64"
              decoding="async"
              fetchpriority="high"
              class="h-16 w-auto block"
            />
            <span class="truncate text-3xl font-bold text-primary">JarvisTech</span>
          </a>

          <!-- PCBuilder -->
          <a
            href="http://localhost:8000/"
            class="text-3xl font-bold text-violetdark hover:text-cyan transition-colors"
          >/ PCBuilder</a>
        </div>

        <!-- Bouton burger -->
        <button
          class="md:hidden p-2 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan"
          @click="toggle"
          :aria-expanded="open ? 'true' : 'false'"
          aria-controls="mobile-menu"
          aria-label="Ouvrir le menu"
        >
          <svg v-if="!open" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 transition-transform duration-200 rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Menu desktop -->
        <nav class="hidden md:flex items-center gap-1">
          <Link
            href="/builds/create"
            :class="[
              'px-3 py-2 rounded-lg text-white/80 hover:text-white hover:bg-white/5 transition-colors',
              isActive('/builds/create') && 'bg-white/10 text-white'
            ]"
          >Créer un Build</Link>

          <Link
            href="/builds"
            :class="[
              'px-2 py-2 rounded-lg text-white/80 hover:text-white hover:bg-white/5 transition-colors',
              isActive('/builds') && 'bg-white/10 text-white'
            ]"
          >Tous les Builds</Link>

          <Link
            href="/components"
            :class="[
              'px-2 py-2 rounded-lg text-white/80 hover:text-white hover:bg-white/5 transition-colors',
              isActive('/components') && 'bg-white/10 text-white'
            ]"
          >Tous les composants</Link>

          <!-- Panier (route nommée) -->
          <Link
            :href="route('checkout.index')"
            class="relative inline-flex items-center gap-2 px-3 py-2 rounded-lg text-white/80 hover:text-white hover:bg-white/5 transition-colors ml-1"
            aria-label="Voir le panier"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M3 3h2l.4 2M7 13h10l3-8H6.4M7 13l-1.293 2.586A1 1 0 0 0 6.618 17h10.764a1 1 0 0 0 .911-.586L20 13M7 13l2 8m6-8-2 8M9 21h6" />
            </svg>
            <span>Panier</span>
            <span
              v-if="count"
              class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1 rounded-full text-xs flex items-center justify-center bg-blue-600 text-white"
            >{{ count }}</span>
          </Link>

          <a
            href="https://jarvistech.be/#contact" target="_blank" rel="noopener noreferrer"
            class="px-2 py-2 rounded-lg text-white/80 hover:text-white hover:bg-white/5 transition-colors"
          >Contact</a>

          <template v-if="user">
            <template v-if="isAdmin">
              <Link
                href="/admin/dashboard"
                :class="[
                  'px-2 py-2 rounded-lg text-white/80 hover:text-white hover:bg-white/5 transition-colors',
                  isActive('/admin/dashboard') && 'bg-white/10 text-white'
                ]"
              >Dashboard Admin</Link>

              <Link
                href="/admin/compatibility-rules"
                :class="[
                  'px-2 py-2 rounded-lg text-white/80 hover:text-white hover:bg-white/5 transition-colors',
                  isActive('/admin/compatibility-rules') && 'bg-white/10 text-white'
                ]"
              >Compatibilités</Link>
            </template>

            <Link
              href="/dashboard"
              :class="[
                'px-3 py-2 rounded-lg text-white/80 hover:text-white hover:bg-white/5 transition-colors',
                isActive('/dashboard') && 'bg-white/10 text-white'
              ]"
            >Dashboard</Link>

            <Link
              href="/profile"
              :class="[
                'px-3 py-2 rounded-lg text-white/80 hover:text-white hover:bg-white/5 transition-colors',
                isActive('/profile') && 'bg-white/10 text-white'
              ]"
            >{{ user.name }}</Link>

            <button
              @click="logout"
              class="ml-1 h-10 px-4 flex items-center bg-red-600 hover:bg-red-700 text-white rounded-xl shadow"
            >Se déconnecter</button>
          </template>

          <template v-else>
            <Link href="/login" class="h-10 px-4 flex items-center bg-primary hover:bg-cyan text-white rounded-xl shadow">Se connecter</Link>
            <Link href="/register" class="h-10 px-4 flex items-center bg-secondary hover:bg-cyan text-white rounded-xl shadow">S'enregistrer</Link>
          </template>
        </nav>
      </div>
    </div>

    <!-- Overlay + Menu mobile -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div v-show="open" class="md:hidden relative">
        <div class="absolute inset-0 bg-black/30" @click="close"></div>

        <div id="mobile-menu" class="relative mx-auto max-w-6xl px-4 sm:px-6 pb-4">
          <div class="flex flex-col gap-1 text-white">
            <!-- Panier mobile (route nommée) -->
            <Link :href="route('checkout.index')" class="px-3 py-2 rounded-lg hover:bg-white/5 flex items-center justify-between" @click="close">
              <span>Panier</span>
              <span v-if="count" class="text-xs px-2 py-0.5 rounded-full bg-blue-600 text-white">{{ count }}</span>
            </Link>

            <Link href="/builds/create" class="px-3 py-2 rounded-lg hover:bg-white/5" @click="close">Créer un Build</Link>
            <Link href="/builds" class="px-3 py-2 rounded-lg hover:bg-white/5" @click="close">Tous les Builds</Link>
            <Link href="/components" class="px-3 py-2 rounded-lg hover:bg-white/5" @click="close">Tous les composants</Link>
            <a href="https://jarvistech.be/#contact" target="_blank" rel="noopener" class="px-3 py-2 rounded-lg hover:bg-white/5" @click="close">Contact</a>

            <template v-if="user">
              <template v-if="isAdmin">
                <Link href="/admin/dashboard" class="px-3 py-2 rounded-lg hover:bg-white/5" @click="close">Dashboard Admin</Link>
                <Link href="/admin/compatibility-rules" class="px-3 py-2 rounded-lg hover:bg-white/5" @click="close">Compatibilités</Link>
              </template>

              <Link href="/dashboard" class="px-3 py-2 rounded-lg hover:bg-white/5" @click="close">Dashboard</Link>
              <Link href="/profile" class="px-3 py-2 rounded-lg hover:bg-white/5" @click="close">{{ user.name }}</Link>
              <button @click="() => { logout(); close(); }" class="mt-2 bg-red-600 hover:bg-red-700 px-4 py-2 rounded-xl shadow">Se déconnecter</button>
            </template>

            <template v-else>
              <Link href="/login" class="bg-primary hover:bg-cyan px-4 py-2 rounded-xl shadow text-center" @click="close">Se connecter</Link>
              <Link href="/register" class="bg-secondary hover:bg-cyan px-4 py-2 rounded-xl shadow text-center" @click="close">S'enregistrer</Link>
            </template>
          </div>
        </div>
      </div>
    </Transition>
  </header>

  <div id="main"></div>
</template>
