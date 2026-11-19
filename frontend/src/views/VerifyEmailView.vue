<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import ProblemAlert from '@/components/ProblemAlert.vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()
const status = ref<'pending' | 'verified' | 'failed'>('pending')

// Le lien est consommé dès l'ouverture de la page : un seul clic dans l'e-mail suffit.
onMounted(async () => {
  const token = typeof route.query.token === 'string' ? route.query.token : ''
  status.value = token !== '' && (await auth.verifyEmail(token)) ? 'verified' : 'failed'
})
</script>

<template>
  <section class="card narrow">
    <h1>Vérification de l'adresse e-mail</h1>
    <p v-if="status === 'pending'" class="muted">Vérification en cours…</p>
    <template v-else-if="status === 'verified'">
      <p class="success" role="status">
        Adresse confirmée. Vous recevrez désormais les e-mails de nouvelles réservations.
      </p>
      <p><RouterLink to="/">Aller à mes agendas</RouterLink></p>
    </template>
    <template v-else>
      <ProblemAlert v-if="auth.problem" :problem="auth.problem" />
      <p v-else class="muted">Ce lien est incomplet.</p>
      <p class="muted">Connectez-vous pour recevoir un nouveau lien.</p>
    </template>
  </section>
</template>
