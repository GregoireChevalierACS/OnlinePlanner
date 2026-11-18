import { vi } from 'vitest'
import { createApp } from 'vue'
import { createPinia, setActivePinia, type Pinia } from 'pinia'
import { authGatewayKey, type AuthGateway } from '@/api/AuthGateway'
import { schedulingGatewayKey, type SchedulingGateway } from '@/api/SchedulingGateway'
import type { Appointment, Slot } from '@/api/types'

/** Double de test du port : chaque méthode est un espion configurable. */
export function fakeGateway() {
  return {
    myProviders: vi.fn<SchedulingGateway['myProviders']>().mockResolvedValue([]),
    registerProvider: vi.fn<SchedulingGateway['registerProvider']>().mockResolvedValue('p1'),
    availableSlots: vi.fn<SchedulingGateway['availableSlots']>().mockResolvedValue([]),
    book: vi.fn<SchedulingGateway['book']>().mockResolvedValue('a1'),
    agenda: vi.fn<SchedulingGateway['agenda']>().mockResolvedValue([]),
    confirm: vi.fn<SchedulingGateway['confirm']>().mockResolvedValue(undefined),
    cancel: vi.fn<SchedulingGateway['cancel']>().mockResolvedValue(undefined),
  } satisfies SchedulingGateway
}

export const account = { id: 'acc-1', email: 'jane@example.com' }

export function fakeAuthGateway() {
  return {
    currentAccount: vi.fn<AuthGateway['currentAccount']>().mockResolvedValue(null),
    login: vi.fn<AuthGateway['login']>().mockResolvedValue(account),
    register: vi.fn<AuthGateway['register']>().mockResolvedValue(account),
    logout: vi.fn<AuthGateway['logout']>().mockResolvedValue(undefined),
    requestPasswordReset: vi.fn<AuthGateway['requestPasswordReset']>().mockResolvedValue(undefined),
    resetPassword: vi.fn<AuthGateway['resetPassword']>().mockResolvedValue(undefined),
  } satisfies AuthGateway
}

/** Exécute `fn` dans une application Vue qui fournit les gateways (comme main.ts). */
export function withGateway<T>(
  gateway: SchedulingGateway,
  fn: () => T,
  auth: AuthGateway = fakeAuthGateway(),
): { result: T; pinia: Pinia } {
  const app = createApp({})
  app.provide(schedulingGatewayKey, gateway)
  app.provide(authGatewayKey, auth)
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
