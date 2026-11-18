<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import ProblemAlert from '@/components/ProblemAlert.vue'
import { safeRedirect } from '@/composables/authGuard'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const mode = ref<'login' | 'register'>('login')
const email = ref('')
const password = ref('')

async function submit(): Promise<void> {
  const action = mode.value === 'login' ? auth.login : auth.register
  if (await action(email.value.trim(), password.value)) {
    password.value = ''
    await router.push(safeRedirect(route.query.redirect))
  }
}
</script>

<template>
  <section class="card narrow">
    <h1>{{ mode === 'login' ? 'Connexion' : 'Créer un compte' }}</h1>
    <ProblemAlert v-if="auth.problem" :problem="auth.problem" />

    <form class="form" @submit.prevent="submit">
      <label>
        E-mail
        <input v-model="email" name="email" type="email" required autocomplete="username" />
      </label>
      <label>
        Mot de passe
        <input
          v-model="password"
          name="password"
          type="password"
          required
          :minlength="mode === 'register' ? 12 : undefined"
          :autocomplete="mode === 'login' ? 'current-password' : 'new-password'"
        />
        <small v-if="mode === 'register'" class="muted">12 caractères minimum.</small>
      </label>
      <button type="submit" class="primary" :disabled="auth.submitting">
        {{ mode === 'login' ? 'Se connecter' : 'Créer mon compte' }}
      </button>
    </form>

    <p class="muted">
      <template v-if="mode === 'login'">
        <RouterLink :to="{ name: 'forgot-password' }">Mot de passe oublié ?</RouterLink>
        <br />
        Pas encore de compte ?
        <button type="button" class="link" @click="mode = 'register'">Créer un compte</button>
      </template>
      <template v-else>
        Déjà inscrit ?
        <button type="button" class="link" @click="mode = 'login'">Se connecter</button>
      </template>
    </p>
  </section>
</template>
