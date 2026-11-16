<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { toProblem } from '@/api/ApiError'
import { useSchedulingGateway } from '@/api/SchedulingGateway'
import type { NewProvider, Problem } from '@/api/types'
import ProblemAlert from '@/components/ProblemAlert.vue'
import ProviderForm from '@/components/ProviderForm.vue'

const gateway = useSchedulingGateway()
const router = useRouter()

const submitting = ref(false)
const problem = ref<Problem | null>(null)
const existingId = ref('')

async function create(provider: NewProvider): Promise<void> {
  submitting.value = true
  problem.value = null
  try {
    const id = await gateway.registerProvider(provider)
    await router.push({ name: 'agenda', params: { providerId: id } })
  } catch (error) {
    problem.value = toProblem(error)
  }
  submitting.value = false
}

function open(): void {
  void router.push({ name: 'agenda', params: { providerId: existingId.value.trim() } })
}
</script>

<template>
  <section class="card">
    <h1>Créer un agenda</h1>
    <ProblemAlert v-if="problem" :problem="problem" />
    <ProviderForm :disabled="submitting" @submit="create" />
  </section>

  <section class="card">
    <h2>Ouvrir un agenda existant</h2>
    <form class="form inline-form" @submit.prevent="open">
      <input
        v-model="existingId"
        name="providerId"
        placeholder="Identifiant du prestataire"
        required
      />
      <button type="submit">Ouvrir</button>
    </form>
  </section>
</template>
