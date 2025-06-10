<script setup>
import { ref } from 'vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import { Link } from '@inertiajs/vue3'

const showingNavigationDropdown = ref(false)
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Barre de navigation -->
    <nav class="border-b bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center gap-8">
            <!-- Logo -->
            <Link href="/" class="flex items-center gap-2">
              <ApplicationLogo class="h-8 w-8" />
              <span class="text-darknavy font-bold">JarvisTech</span>
            </Link>

            <!-- Liens principaux -->
            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
              Tableau de bord
            </NavLink>
          </div>

          <!-- Utilisateur / Menu -->
          <div class="flex items-center gap-4">
            <!-- Menu Desktop -->
            <Dropdown align="right" width="48">
              <template #trigger>
                <button class="inline-flex items-center text-sm text-gray-700 hover:text-darknavy">
                  {{ $page.props.auth.user.name }}
                  <svg class="ms-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0L5.293 8.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
              </template>

              <template #content>
                <DropdownLink :href="route('profile.edit')">Profil</DropdownLink>
                <DropdownLink :href="route('logout')" method="post" as="button">
                  Se déconnecter
                </DropdownLink>
              </template>
            </Dropdown>

            <!-- Menu Mobile -->
            <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="sm:hidden text-gray-500 hover:text-gray-700">
              <svg class="h-6 w-6" viewBox="0 0 24 24" stroke="currentColor" fill="none">
                <path
                  v-if="!showingNavigationDropdown"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"
                />
                <path
                  v-else
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Menu responsive -->
      <div v-show="showingNavigationDropdown" class="sm:hidden px-4 pb-4">
        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
          Tableau de bord
        </ResponsiveNavLink>
        <div class="border-t mt-4 pt-4">
          <div class="text-sm text-gray-700 mb-1">{{ $page.props.auth.user.name }}</div>
          <div class="text-xs text-gray-500">{{ $page.props.auth.user.email }}</div>
          <ResponsiveNavLink :href="route('profile.edit')">Profil</ResponsiveNavLink>
          <ResponsiveNavLink :href="route('logout')" method="post" as="button">
            Se déconnecter
          </ResponsiveNavLink>
        </div>
      </div>
    </nav>

    <!-- Contenu principal -->
    <main class="py-6 px-4 sm:px-6 lg:px-8">
      <slot />
    </main>
  </div>
</template>
