import { vi } from 'vitest'
import { createApp } from 'vue'
import { createPinia, setActivePinia, type Pinia } from 'pinia'
import { schedulingGatewayKey, type SchedulingGateway } from '@/api/SchedulingGateway'
import type { Appointment, Slot } from '@/api/types'

/** Double de test du port : chaque méthode est un espion configurable. */
export function fakeGateway() {
  return {
    registerProvider: vi.fn<SchedulingGateway['registerProvider']>().mockResolvedValue('p1'),
    availableSlots: vi.fn<SchedulingGateway['availableSlots']>().mockResolvedValue([]),
    book: vi.fn<SchedulingGateway['book']>().mockResolvedValue('a1'),
    agenda: vi.fn<SchedulingGateway['agenda']>().mockResolvedValue([]),
    confirm: vi.fn<SchedulingGateway['confirm']>().mockResolvedValue(undefined),
    cancel: vi.fn<SchedulingGateway['cancel']>().mockResolvedValue(undefined),
  } satisfies SchedulingGateway
}

/** Exécute `fn` dans une application Vue qui fournit le gateway (comme main.ts). */
export function withGateway<T>(
  gateway: SchedulingGateway,
  fn: () => T,
): { result: T; pinia: Pinia } {
  const app = createApp({})
  app.provide(schedulingGatewayKey, gateway)
  const pinia = createPinia()
  app.use(pinia)
  setActivePinia(pinia)
  return { result: app.runWithContext(fn), pinia }
}

export const slot = (time: string, day = '2026-01-05'): Slot => ({
  startsAt: `${day}T${time}:00+00:00`,
  endsAt: `${day}T${time}:30+00:00`,
})

export const appointment = (overrides: Partial<Appointment> = {}): Appointment => ({
  id: 'a1',
  providerId: 'p1',
  customerName: 'Jane Doe',
  customerEmail: 'jane@example.com',
  startsAt: '2026-01-05T09:00:00+00:00',
  endsAt: '2026-01-05T09:30:00+00:00',
  status: 'pending',
  ...overrides,
})

/** Promesse contrôlable depuis le test, pour simuler des réponses qui arrivent dans le désordre. */
export function deferred<T>() {
  let resolve!: (value: T) => void
  let reject!: (reason: unknown) => void
  const promise = new Promise<T>((res, rej) => {
    resolve = res
    reject = rej
  })
  return { promise, resolve, reject }
}
