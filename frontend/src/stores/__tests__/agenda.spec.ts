import { describe, expect, it } from 'vitest'
import { ApiError } from '@/api/ApiError'
import type { Appointment } from '@/api/types'
import { appointment, deferred, fakeGateway, withGateway } from '@/__tests__/fakeGateway'
import { useAgendaStore } from '../agenda'

function setup(gateway = fakeGateway()) {
  return { gateway, store: withGateway(gateway, () => useAgendaStore()).result }
}

describe('agenda store', () => {
  it('charge la période et regroupe les rendez-vous par jour', async () => {
    const { gateway, store } = setup()
    gateway.agenda.mockResolvedValue([
      appointment({ id: 'a1', startsAt: '2026-01-05T09:00:00+00:00' }),
      appointment({ id: 'a2', startsAt: '2026-01-05T10:00:00+00:00' }),
      appointment({ id: 'a3', startsAt: '2026-01-06T09:00:00+00:00' }),
    ])

    const loading = store.load('p1', '2026-01-05', '2026-01-11')
    expect(store.loading).toBe(true)
    await loading

    expect(gateway.agenda).toHaveBeenCalledWith(
      'p1',
      '2026-01-05',
      '2026-01-11',
      expect.any(AbortSignal),
    )
    expect(store.loading).toBe(false)
    expect(store.byDay.map((g) => [g.day, g.items.map((a) => a.id)])).toEqual([
      ['2026-01-05', ['a1', 'a2']],
      ['2026-01-06', ['a3']],
    ])
  })

  it("ignore la réponse d'une période précédente arrivée en retard", async () => {
    const { gateway, store } = setup()
    const old = deferred<Appointment[]>()
    gateway.agenda
      .mockReturnValueOnce(old.promise)
      .mockResolvedValueOnce([appointment({ id: 'new' })])

    const first = store.load('p1', '2026-01-01', '2026-01-02')
    await store.load('p1', '2026-01-05', '2026-01-11')
    old.resolve([appointment({ id: 'old' })])
    await first

    expect(store.appointments.map((a) => a.id)).toEqual(['new'])
    expect(store.from).toBe('2026-01-05')
  })

  it('affiche un problème si le chargement échoue', async () => {
    const { gateway, store } = setup()
    gateway.agenda.mockRejectedValue(
      new ApiError({ status: 404, title: 'Not Found', detail: 'Prestataire introuvable.' }),
    )

    await store.load('inconnu', '2026-01-05', '2026-01-11')

    expect(store.appointments).toEqual([])
    expect(store.problem?.detail).toBe('Prestataire introuvable.')
    expect(store.loading).toBe(false)
  })

  it('confirme puis recharge la même période', async () => {
    const { gateway, store } = setup()
    await store.load('p1', '2026-01-05', '2026-01-11')

    await store.confirm('a1')

    expect(gateway.confirm).toHaveBeenCalledWith('a1')
    expect(gateway.agenda).toHaveBeenCalledTimes(2)
    expect(gateway.agenda).toHaveBeenLastCalledWith(
      'p1',
      '2026-01-05',
      '2026-01-11',
      expect.any(AbortSignal),
    )
  })

  it('annule avec le motif puis recharge', async () => {
    const { gateway, store } = setup()
    await store.load('p1', '2026-01-05', '2026-01-11')

    await store.cancel('a1', 'Empêchement')

    expect(gateway.cancel).toHaveBeenCalledWith('a1', 'Empêchement')
    expect(gateway.agenda).toHaveBeenCalledTimes(2)
  })

  it('affiche le refus du serveur sans recharger', async () => {
    const { gateway, store } = setup()
    gateway.confirm.mockRejectedValue(
      new ApiError({ status: 409, title: 'Conflict', detail: 'Action impossible.' }),
    )
    await store.load('p1', '2026-01-05', '2026-01-11')

    await store.confirm('a1')

    expect(store.problem?.detail).toBe('Action impossible.')
    expect(gateway.agenda).toHaveBeenCalledTimes(1)
  })
})
