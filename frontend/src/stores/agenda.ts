import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { toProblem } from '@/api/ApiError'
import { useSchedulingGateway } from '@/api/SchedulingGateway'
import type { Appointment, Problem } from '@/api/types'
import { useLatestRequest } from '@/composables/useLatestRequest'
import { dayOf } from '@/composables/dates'

/** Agenda du prestataire sur une période, avec confirmation et annulation. */
export const useAgendaStore = defineStore('agenda', () => {
  const gateway = useSchedulingGateway()
  const latest = useLatestRequest()

  const providerId = ref('')
  const from = ref('')
  const to = ref('')
  const appointments = ref<Appointment[]>([])
  const loading = ref(false)
  const problem = ref<Problem | null>(null)

  /** Rendez-vous regroupés par jour, dans l'ordre chronologique (état dérivé, jamais stocké). */
  const byDay = computed(() => {
    const groups = new Map<string, Appointment[]>()
    for (const appointment of appointments.value) {
      const key = dayOf(appointment.startsAt)
      groups.set(key, [...(groups.get(key) ?? []), appointment])
    }
    return [...groups.entries()].map(([day, items]) => ({ day, items }))
  })

  async function load(forProvider: string, fromDay: string, toDay: string): Promise<void> {
    providerId.value = forProvider
    from.value = fromDay
    to.value = toDay
    problem.value = null
    loading.value = true
    try {
      const result = await latest((signal) => gateway.agenda(forProvider, fromDay, toDay, signal))
      if (result.stale) {
        return
      }
      appointments.value = result.value
    } catch (error) {
      appointments.value = []
      problem.value = toProblem(error)
    }
    loading.value = false
  }

  async function act(action: () => Promise<void>): Promise<void> {
    problem.value = null
    try {
      await action()
    } catch (error) {
      problem.value = toProblem(error)
      return
    }
    await load(providerId.value, from.value, to.value)
  }

  const confirm = (appointmentId: string) => act(() => gateway.confirm(appointmentId))
  const cancel = (appointmentId: string, reason: string) =>
    act(() => gateway.cancel(appointmentId, reason))

  return { providerId, from, to, appointments, loading, problem, byDay, load, confirm, cancel }
})
