<script setup>
import { ref, computed } from 'vue'
import Checkbox from '@/Components/Checkbox.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  canResetPassword: { type: Boolean, default: false },
  status: { type: String, default: '' },
})

const showPwd = ref(false)

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

function submit() {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}

const disabled = computed(() => form.processing || !form.email || !form.password)
</script>

<template>
  <GuestLayout>
    <Head title="Se connecter" />

    <div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4">
      <div class="w-full max-w-md">
        <!-- Carte -->
        <div class="relative bg-white rounded-2xl shadow-2xl px-6 py-8 sm:px-8">
          <!-- En-tête visuel -->
          <div class="flex items-center gap-3 mb-6">
            <img
              src="https://res.cloudinary.com/djllwl8c0/image/upload/f_auto,q_auto,w_192,h_48,c_fit/Logo-JarvisTech-PNG-normalsansfond_pgxlrj.png"
              alt="JarvisTech"
              class="h-10 w-auto"
            />
            <span class="text-xl font-bold text-[#1ec3a6]">PCBuilder</span>
          </div>

          <h1 class="text-2xl font-extrabold text-slate-900">Connexion</h1>
          <p class="mt-1 text-sm text-slate-600">Ravi de vous revoir 👋</p>

          <!-- Statut (ex: email de réinitialisation envoyé) -->
          <div
            v-if="props.status"
            class="mt-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2 text-sm"
          >
            {{ props.status }}
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submit">
            <!-- Email -->
            <div>
              <InputLabel for="email" value="Adresse e-mail" />
              <div class="relative">
                <TextInput
                  id="email"
                  type="email"
                  class="mt-1 block w-full pr-10"
                  v-model="form.email"
                  required
                  autofocus
                  autocomplete="username"
                />
                <!-- icône -->
                <svg class="pointer-events-none absolute right-3 top-3.5 h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none">
                  <path d="M4 6l8 6 8-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <rect x="3" y="5" width="18" height="14" rx="2" ry="2" stroke="currentColor" stroke-width="2" fill="none"/>
                </svg>
              </div>
              <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Mot de passe -->
            <div>
              <InputLabel for="password" value="Mot de passe" />
              <div class="relative">
                <TextInput
                  :type="showPwd ? 'text' : 'password'"
                  id="password"
                  class="mt-1 block w-full pr-10"
                  v-model="form.password"
                  required
                  autocomplete="current-password"
                />
                <button
                  type="button"
                  class="absolute right-2 top-2.5 rounded-md px-2 py-1 text-slate-500 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary"
                  @click="showPwd = !showPwd"
                  :aria-label="showPwd ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                >
                  <svg v-if="!showPwd" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="2"/>
                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path d="M3 3l18 18M10.6 10.6a3 3 0 104.24 4.24M9.88 4.24C10.57 4.08 11.28 4 12 4c6.4 0 10 8 10 8a18.7 18.7 0 01-5.09 5.91M6.2 6.2A18.5 18.5 0 002 12s3.6 7 10 7c1.78 0 3.37-.47 4.76-1.23" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </button>
              </div>
              <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Remember me & lien reset -->
            <div class="flex items-center justify-between">
              <label class="flex items-center select-none">
                <Checkbox name="remember" v-model:checked="form.remember" />
                <span class="ms-2 text-sm text-gray-700">Se souvenir de moi</span>
              </label>

              <Link
                v-if="props.canResetPassword"
                :href="route('password.request')"
                class="text-sm text-primary hover:text-cyan underline"
              >
                Mot de passe oublié ?
              </Link>
            </div>

            <!-- Submit -->
            <PrimaryButton
              class="w-full h-11 justify-center bg-primary hover:bg-cyan text-white rounded-xl shadow
                     focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary
                     disabled:opacity-60 disabled:cursor-not-allowed"
              :class="{ 'opacity-75': form.processing }"
              :disabled="disabled"
            >
              {{ form.processing ? 'Connexion…' : 'Se connecter' }}
            </PrimaryButton>

            <!-- Lien inscription -->
            <p class="text-center text-sm text-slate-600">
              Pas de compte ?
              <Link :href="route('register')" class="text-primary hover:text-cyan underline">S’enregistrer</Link>
            </p>
          </form>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>
