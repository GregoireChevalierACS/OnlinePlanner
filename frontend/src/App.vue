<script setup lang="ts">
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import VerifyEmailBanner from '@/components/VerifyEmailBanner.vue'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

// Changement de page : on n'affiche plus l'erreur du formulaire précédent.
router.afterEach(() => auth.clearProblem())

async function logout(): Promise<void> {
  await auth.logout()
  await router.push({ name: 'login' })
}
</script>

<template>
  <header class="topbar">
    <RouterLink to="/" class="brand">OnlinePlanner</RouterLink>
    <nav>
      <template v-if="typeof route.params.providerId === 'string' && auth.isAuthenticated">
        <RouterLink :to="{ name: 'agenda', params: { providerId: route.params.providerId } }">
          Agenda
        </RouterLink>
        <RouterLink :to="{ name: 'booking', params: { providerId: route.params.providerId } }">
          Réservation
        </RouterLink>
      </template>
      <template v-if="auth.account">
        <span class="muted">{{ auth.account.email }}</span>
        <button type="button" @click="logout">Se déconnecter</button>
      </template>
    </nav>
  </header>

  <main class="container">
    <VerifyEmailBanner v-if="auth.needsEmailVerification" />
    <RouterView />
  </main>
</template>
