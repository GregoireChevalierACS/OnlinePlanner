<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import ProblemAlert from '@/components/ProblemAlert.vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()

const token = computed(() => (typeof route.query.token === 'string' ? route.query.token : ''))
const password = ref('')
const confirmation = ref('')
const done = ref(false)
const mismatch = computed(() => confirmation.value !== '' && confirmation.value !== password.value)

async function submit(): Promise<void> {
  if (mismatch.value) {
    return
  }
  done.value = await auth.resetPassword(token.value, password.value)
  password.value = ''
  confirmation.value = ''
}
</script>

<template>
  <section class="card narrow">
    <h1>Nouveau mot de passe</h1>
    <ProblemAlert v-if="auth.problem" :problem="auth.problem" />

    <template v-if="done">
      <p class="success" role="status">Votre mot de passe a été changé.</p>
      <p><RouterLink :to="{ name: 'login' }">Se connecter</RouterLink></p>
    </template>
    <p v-else-if="token === ''" class="muted">
      Ce lien est incomplet.
      <RouterLink :to="{ name: 'forgot-password' }">Demander un nouveau lien</RouterLink>
    </p>
    <form v-else class="form" @submit.prevent="submit">
      <label>
        Nouveau mot de passe
        <input
          v-model="password"
          name="password"
          type="password"
          required
          minlength="12"
          autocomplete="new-password"
        />
        <small class="muted">12 caractères minimum.</small>
      </label>
      <label>
        Confirmation
        <input
          v-model="confirmation"
          name="confirmation"
          type="password"
          required
          autocomplete="new-password"
        />
        <small v-if="mismatch" class="danger-text">Les deux mots de passe sont différents.</small>
      </label>
      <button type="submit" class="primary" :disabled="auth.submitting || mismatch">
        Changer le mot de passe
      </button>
    </form>
  </section>
</template>
