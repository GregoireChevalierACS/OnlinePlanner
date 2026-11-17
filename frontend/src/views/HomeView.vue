<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { toProblem } from '@/api/ApiError'
import { useSchedulingGateway } from '@/api/SchedulingGateway'
import type { NewProvider, Problem, ProviderSummary } from '@/api/types'
import ProblemAlert from '@/components/ProblemAlert.vue'
import ProviderForm from '@/components/ProviderForm.vue'

const gateway = useSchedulingGateway()
const router = useRouter()

const providers = ref<ProviderSummary[]>([])
const submitting = ref(false)
const problem = ref<Problem | null>(null)

onMounted(async () => {
  try {
    providers.value = await gateway.myProviders()
  } catch (error) {
    problem.value = toProblem(error)
  }
})

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
</script>

<template>
  <ProblemAlert v-if="problem" :problem="problem" />

  <section v-if="providers.length > 0" class="card">
    <h1>Mes agendas</h1>
    <ul class="providers">
      <li v-for="provider in providers" :key="provider.id">
        <RouterLink :to="{ name: 'agenda', params: { providerId: provider.id } }">
          {{ provider.name }}
        </RouterLink>
      </li>
    </ul>
  </section>

  <section class="card">
    <h2>Créer un agenda</h2>
    <ProviderForm :disabled="submitting" @submit="create" />
  </section>
</template>
