<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import type { Appointment, AppointmentStatus } from '@/api/types'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import ProblemAlert from '@/components/ProblemAlert.vue'
import { addDays, isoDate, timeOf } from '@/composables/dates'
import { useAgendaStore } from '@/stores/agenda'

const props = defineProps<{ providerId: string }>()
const agenda = useAgendaStore()

const from = ref(isoDate(new Date()))
const to = ref(addDays(from.value, 6))
const toCancel = ref<Appointment | null>(null)

const bookingUrl = computed(
  () => `${window.location.origin}/p/${encodeURIComponent(props.providerId)}/reserver`,
)

const statusLabels: Record<AppointmentStatus, string> = {
  pending: 'À confirmer',
  confirmed: 'Confirmé',
  cancelled: 'Annulé',
  completed: 'Terminé',
}

watch(
  [() => props.providerId, from, to],
  ([providerId, fromDay, toDay]) => agenda.load(providerId, fromDay, toDay),
  {
    immediate: true,
  },
)

async function cancelConfirmed(reason: string): Promise<void> {
  const appointment = toCancel.value
  toCancel.value = null
  if (appointment !== null) {
    await agenda.cancel(appointment.id, reason)
  }
}
</script>

<template>
  <section class="card">
    <h1>Agenda</h1>
    <p class="muted">
      Lien de réservation à partager : <a :href="bookingUrl">{{ bookingUrl }}</a>
    </p>

    <div class="grid">
      <label>
        Du
        <input v-model="from" type="date" name="from" required />
      </label>
      <label>
        Au
        <input v-model="to" type="date" name="to" :min="from" required />
      </label>
    </div>

    <ProblemAlert v-if="agenda.problem" :problem="agenda.problem" />
    <p v-if="agenda.loading" class="muted">Chargement…</p>
    <p v-else-if="agenda.byDay.length === 0" class="muted">Aucun rendez-vous sur cette période.</p>

    <div v-for="group in agenda.byDay" :key="group.day" class="day">
      <h2>{{ group.day }}</h2>
      <ul class="appointments">
        <li
          v-for="appointment in group.items"
          :key="appointment.id"
          :class="['appointment', appointment.status]"
        >
          <span class="time"
            >{{ timeOf(appointment.startsAt) }}–{{ timeOf(appointment.endsAt) }}</span
          >
          <span class="who">{{ appointment.customerName }} · {{ appointment.customerEmail }}</span>
          <span class="status">{{ statusLabels[appointment.status] }}</span>
          <span class="actions">
            <button
              v-if="appointment.status === 'pending'"
              type="button"
              @click="agenda.confirm(appointment.id)"
            >
              Confirmer
            </button>
            <button
              v-if="appointment.status === 'pending' || appointment.status === 'confirmed'"
              type="button"
              class="danger"
              @click="toCancel = appointment"
            >
              Annuler
            </button>
          </span>
        </li>
      </ul>
    </div>
  </section>

  <ConfirmDialog
    v-if="toCancel"
    title="Annuler ce rendez-vous ?"
    :message="`${toCancel.customerName}, ${toCancel.startsAt.slice(0, 10)} à ${timeOf(toCancel.startsAt)}. Cette action est définitive.`"
    confirm-label="Annuler le rendez-vous"
    ask-reason
    @confirm="cancelConfirmed"
    @close="toCancel = null"
  />
</template>
