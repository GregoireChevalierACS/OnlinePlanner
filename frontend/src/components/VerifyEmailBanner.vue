<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const sent = ref(false)

async function resend(): Promise<void> {
  sent.value = await auth.resendEmailVerification()
}
</script>

<template>
  <div class="banner" role="status">
    <span>
      Confirmez votre adresse <strong>{{ auth.account?.email }}</strong> pour recevoir les e-mails
      de nouvelles réservations.
    </span>
    <span v-if="sent" class="muted">Lien envoyé, vérifiez votre boîte de réception.</span>
    <button v-else type="button" :disabled="auth.submitting" @click="resend">
      Renvoyer le lien
    </button>
  </div>
</template>
