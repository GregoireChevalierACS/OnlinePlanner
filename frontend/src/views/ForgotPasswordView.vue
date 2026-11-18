<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import ProblemAlert from '@/components/ProblemAlert.vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const email = ref('')
const sent = ref(false)

async function submit(): Promise<void> {
  sent.value = await auth.requestPasswordReset(email.value.trim())
}
</script>

<template>
  <section class="card narrow">
    <h1>Mot de passe oublié</h1>
    <ProblemAlert v-if="auth.problem" :problem="auth.problem" />

    <p v-if="sent" class="success" role="status">
      Si un compte existe pour cette adresse, un e-mail contenant un lien de réinitialisation vient
      d'être envoyé. Le lien est valable 1 heure.
    </p>
    <form v-else class="form" @submit.prevent="submit">
      <label>
        E-mail du compte
        <input v-model="email" name="email" type="email" required autocomplete="username" />
      </label>
      <button type="submit" class="primary" :disabled="auth.submitting">Envoyer le lien</button>
    </form>

    <p class="muted"><RouterLink :to="{ name: 'login' }">Retour à la connexion</RouterLink></p>
  </section>
</template>
