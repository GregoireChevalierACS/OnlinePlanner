import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { toProblem } from '@/api/ApiError'
import { useSchedulingGateway } from '@/api/SchedulingGateway'
import type { Problem, Slot } from '@/api/types'
import { useLatestRequest } from '@/composables/useLatestRequest'

export interface Customer {
  name: string
  email: string
}

/** Parcours de réservation côté client : jour → créneau → coordonnées. */
export const useBookingStore = defineStore('booking', () => {
  const gateway = useSchedulingGateway()
  const latest = useLatestRequest()

  const providerId = ref('')
  const day = ref('')
  const slots = ref<Slot[]>([])
  const selected = ref<Slot | null>(null)
  const loading = ref(false)
  const submitting = ref(false)
  const problem = ref<Problem | null>(null)
  const bookedSlot = ref<Slot | null>(null)

  const canBook = computed(() => selected.value !== null && !submitting.value)

  async function loadSlots(forProvider: string, forDay: string): Promise<void> {
    providerId.value = forProvider
    day.value = forDay
    selected.value = null
    problem.value = null
    loading.value = true
    try {
      const result = await latest((signal) => gateway.availableSlots(forProvider, forDay, signal))
      if (result.stale) {
        return
      }
      slots.value = result.value
    } catch (error) {
      slots.value = []
      problem.value = toProblem(error)
    }
    loading.value = false
  }

  function select(slot: Slot): void {
    selected.value = slot
    bookedSlot.value = null
  }

  async function book(customer: Customer): Promise<void> {
    const slot = selected.value
    if (slot === null || submitting.value) {
      return
    }
    submitting.value = true
    problem.value = null
    try {
      await gateway.book(providerId.value, {
        startsAt: slot.startsAt,
        customerName: customer.name,
        customerEmail: customer.email,
      })
      bookedSlot.value = slot
    } catch (error) {
      problem.value = toProblem(error)
    }
    submitting.value = false
    // Dans tous les cas l'agenda a pu changer (réservé par nous ou par quelqu'un d'autre) : on recharge.
    const failure = problem.value
    await loadSlots(providerId.value, day.value)
    problem.value = failure
  }

  return {
    providerId,
    day,
    slots,
    selected,
    loading,
    submitting,
    problem,
    bookedSlot,
    canBook,
    loadSlots,
    select,
    book,
  }
})
