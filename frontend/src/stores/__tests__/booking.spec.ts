import { describe, expect, it } from 'vitest'
import { ApiError } from '@/api/ApiError'
import { deferred, fakeGateway, slot, withGateway } from '@/__tests__/fakeGateway'
import { useBookingStore } from '../booking'

const conflict = new ApiError({
  status: 409,
  title: 'Conflict',
  detail: 'Créneau indisponible : déjà réservé.',
  reasons: ['déjà réservé'],
})

function setup(gateway = fakeGateway()) {
  return { gateway, store: withGateway(gateway, () => useBookingStore()).result }
}

describe('booking store', () => {
  it('charge les créneaux du jour et réinitialise la sélection', async () => {
    const { gateway, store } = setup()
    gateway.availableSlots.mockResolvedValue([slot('09:00'), slot('09:30')])
    store.select(slot('08:00'))

    const loading = store.loadSlots('p1', '2026-01-05')
    expect(store.loading).toBe(true)
    expect(store.selected).toBeNull()
    await loading

    expect(gateway.availableSlots).toHaveBeenCalledWith('p1', '2026-01-05', expect.any(AbortSignal))
    expect(store.slots).toEqual([slot('09:00'), slot('09:30')])
    expect(store.loading).toBe(false)
    expect(store.providerId).toBe('p1')
    expect(store.day).toBe('2026-01-05')
  })

  it("ignore la réponse d'un jour précédent arrivée en retard", async () => {
    const { gateway, store } = setup()
    const monday = deferred<ReturnType<typeof slot>[]>()
    gateway.availableSlots
      .mockReturnValueOnce(monday.promise)
      .mockResolvedValueOnce([slot('14:00', '2026-01-06')])

    const first = store.loadSlots('p1', '2026-01-05')
    await store.loadSlots('p1', '2026-01-06')
    monday.resolve([slot('09:00')])
    await first

    expect(store.slots).toEqual([slot('14:00', '2026-01-06')])
    expect(store.loading).toBe(false)
  })

  it('affiche un problème et vide les créneaux si le chargement échoue', async () => {
    const { gateway, store } = setup()
    gateway.availableSlots
      .mockResolvedValueOnce([slot('09:00')])
      .mockRejectedValueOnce(new TypeError('Failed to fetch'))
    await store.loadSlots('p1', '2026-01-05')

    await store.loadSlots('p1', '2026-01-05')

    expect(store.slots).toEqual([])
    expect(store.problem?.title).toBe('Serveur injoignable')
    expect(store.loading).toBe(false)
  })

  it('ne réserve rien sans créneau sélectionné', async () => {
    const { gateway, store } = setup()

    expect(store.canBook).toBe(false)
    await store.book({ name: 'Jane', email: 'jane@example.com' })

    expect(gateway.book).not.toHaveBeenCalled()
  })

  it('réserve le créneau choisi puis recharge les créneaux', async () => {
    const { gateway, store } = setup()
    gateway.availableSlots.mockResolvedValue([slot('09:00')])
    await store.loadSlots('p1', '2026-01-05')
    store.select(slot('09:00'))
    expect(store.canBook).toBe(true)

    const booking = store.book({ name: 'Jane', email: 'jane@example.com' })
    expect(store.submitting).toBe(true)
    expect(store.canBook).toBe(false)
    await store.book({ name: 'Doublon', email: 'x@example.com' })
    await booking

    expect(gateway.book).toHaveBeenCalledTimes(1)
    expect(gateway.book).toHaveBeenCalledWith('p1', {
      startsAt: slot('09:00').startsAt,
      customerName: 'Jane',
      customerEmail: 'jane@example.com',
    })
    expect(store.bookedSlot).toEqual(slot('09:00'))
    expect(store.problem).toBeNull()
    expect(store.submitting).toBe(false)
    expect(gateway.availableSlots).toHaveBeenCalledTimes(2)
  })

  it("garde le motif du refus et rafraîchit les créneaux quand le créneau vient d'être pris", async () => {
    const { gateway, store } = setup()
    gateway.availableSlots.mockResolvedValueOnce([slot('09:00')]).mockResolvedValueOnce([])
    gateway.book.mockRejectedValue(conflict)
    await store.loadSlots('p1', '2026-01-05')
    store.select(slot('09:00'))

    await store.book({ name: 'Jane', email: 'jane@example.com' })

    expect(store.problem?.reasons).toEqual(['déjà réservé'])
    expect(store.bookedSlot).toBeNull()
    expect(store.slots).toEqual([])
    expect(store.selected).toBeNull()
  })

  it('efface la confirmation précédente quand on choisit un autre créneau', async () => {
    const { gateway, store } = setup()
    gateway.availableSlots.mockResolvedValue([slot('09:00'), slot('09:30')])
    await store.loadSlots('p1', '2026-01-05')
    store.select(slot('09:00'))
    await store.book({ name: 'Jane', email: 'jane@example.com' })

    store.select(slot('09:30'))

    expect(store.bookedSlot).toBeNull()
  })
})
