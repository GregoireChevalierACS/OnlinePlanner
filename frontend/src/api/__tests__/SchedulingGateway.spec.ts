import { describe, expect, it, vi } from 'vitest'
import { createApp } from 'vue'
import { HttpClient } from '../HttpClient'
import {
  HttpSchedulingGateway,
  schedulingGatewayKey,
  useSchedulingGateway,
} from '../SchedulingGateway'

function gatewayReturning(body: unknown, status = 200) {
  const fetchFn = vi
    .fn<typeof fetch>()
    .mockResolvedValue(
      status === 204
        ? new Response(null, { status })
        : new Response(JSON.stringify(body), { status }),
    )
  return { gateway: new HttpSchedulingGateway(new HttpClient('', fetchFn)), fetchFn }
}

const call = (fetchFn: ReturnType<typeof vi.fn<typeof fetch>>) => {
  const [url, init] = fetchFn.mock.calls[0] ?? []
  return { url, method: init?.method, body: init?.body, signal: init?.signal }
}

describe('HttpSchedulingGateway', () => {
  it('inscrit un prestataire', async () => {
    const { gateway, fetchFn } = gatewayReturning({ id: 'p1' }, 201)
    const provider = {
      name: 'Dr',
      openingHours: [],
      slotDurationMinutes: 30,
      bufferMinutes: 0,
      minimumNoticeMinutes: 0,
    }

    await expect(gateway.registerProvider(provider)).resolves.toBe('p1')
    expect(call(fetchFn)).toMatchObject({
      url: '/api/providers',
      method: 'POST',
      body: JSON.stringify(provider),
    })
  })

  it("liste les créneaux d'un jour", async () => {
    const { gateway, fetchFn } = gatewayReturning([])
    const signal = new AbortController().signal

    await gateway.availableSlots('p/1', '2026-01-05', signal)

    expect(call(fetchFn)).toMatchObject({
      url: '/api/providers/p%2F1/slots?date=2026-01-05',
      method: 'GET',
      signal,
    })
  })

  it('réserve un créneau', async () => {
    const { gateway, fetchFn } = gatewayReturning({ id: 'a1' }, 201)
    const booking = {
      startsAt: '2026-01-05T09:00:00',
      customerName: 'Jane',
      customerEmail: 'j@x.fr',
    }

    await expect(gateway.book('p1', booking)).resolves.toBe('a1')
    expect(call(fetchFn)).toMatchObject({
      url: '/api/providers/p1/appointments',
      method: 'POST',
      body: JSON.stringify(booking),
    })
  })

  it("charge l'agenda d'une période", async () => {
    const { gateway, fetchFn } = gatewayReturning([])
    const signal = new AbortController().signal

    await gateway.agenda('p1', '2026-01-05', '2026-01-11', signal)

    expect(call(fetchFn)).toMatchObject({
      url: '/api/providers/p1/appointments?from=2026-01-05&to=2026-01-11',
      method: 'GET',
      signal,
    })
  })

  it('confirme un rendez-vous', async () => {
    const { gateway, fetchFn } = gatewayReturning(null, 204)

    await gateway.confirm('a 1')

    expect(call(fetchFn)).toMatchObject({ url: '/api/appointments/a%201/confirm', method: 'POST' })
  })

  it('annule un rendez-vous avec un motif', async () => {
    const { gateway, fetchFn } = gatewayReturning(null, 204)

    await gateway.cancel('a1', 'Empêchement')

    expect(call(fetchFn)).toMatchObject({
      url: '/api/appointments/a1/cancel',
      method: 'POST',
      body: '{"reason":"Empêchement"}',
    })
  })
})

describe('useSchedulingGateway', () => {
  it("renvoie le gateway fourni par l'application", () => {
    const app = createApp({})
    const gateway = gatewayReturning([]).gateway
    app.provide(schedulingGatewayKey, gateway)

    expect(app.runWithContext(() => useSchedulingGateway())).toBe(gateway)
  })

  it("échoue explicitement si aucun gateway n'est fourni", () => {
    const app = createApp({})

    expect(() => app.runWithContext(() => useSchedulingGateway())).toThrow(
      'Aucun SchedulingGateway fourni',
    )
  })
})
