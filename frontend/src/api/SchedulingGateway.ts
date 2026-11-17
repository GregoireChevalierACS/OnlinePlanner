import type { InjectionKey } from 'vue'
import { inject } from 'vue'
import type { HttpClient } from './HttpClient'
import type { Appointment, NewAppointment, NewProvider, ProviderSummary, Slot } from './types'

/**
 * Port : ce dont l'interface a besoin, sans savoir comment on parle au serveur.
 * Les stores et les vues ne dépendent que de cette interface (injectée par Vue).
 */
export interface SchedulingGateway {
  /** Agendas gérés par le compte connecté. */
  myProviders(): Promise<ProviderSummary[]>
  registerProvider(provider: NewProvider): Promise<string>
  availableSlots(providerId: string, date: string, signal?: AbortSignal): Promise<Slot[]>
  book(providerId: string, appointment: NewAppointment): Promise<string>
  agenda(providerId: string, from: string, to: string, signal?: AbortSignal): Promise<Appointment[]>
  confirm(appointmentId: string): Promise<void>
  cancel(appointmentId: string, reason: string): Promise<void>
}

export const schedulingGatewayKey: InjectionKey<SchedulingGateway> = Symbol('SchedulingGateway')

export function useSchedulingGateway(): SchedulingGateway {
  const gateway = inject(schedulingGatewayKey)
  if (gateway === undefined) {
    throw new Error(
      'Aucun SchedulingGateway fourni : appeler app.provide(schedulingGatewayKey, …).',
    )
  }
  return gateway
}

/** Adapter HTTP du port, sur l'API Symfony. */
export class HttpSchedulingGateway implements SchedulingGateway {
  constructor(private readonly http: HttpClient) {}

  myProviders(): Promise<ProviderSummary[]> {
    return this.http.request('GET', '/api/providers')
  }

  async registerProvider(provider: NewProvider): Promise<string> {
    return (await this.http.request<{ id: string }>('POST', '/api/providers', provider)).id
  }

  availableSlots(providerId: string, date: string, signal?: AbortSignal): Promise<Slot[]> {
    const query = new URLSearchParams({ date })
    return this.http.request(
      'GET',
      `/api/providers/${encodeURIComponent(providerId)}/slots?${query}`,
      undefined,
      signal,
    )
  }

  async book(providerId: string, appointment: NewAppointment): Promise<string> {
    const path = `/api/providers/${encodeURIComponent(providerId)}/appointments`
    return (await this.http.request<{ id: string }>('POST', path, appointment)).id
  }

  agenda(
    providerId: string,
    from: string,
    to: string,
    signal?: AbortSignal,
  ): Promise<Appointment[]> {
    const query = new URLSearchParams({ from, to })
    return this.http.request(
      'GET',
      `/api/providers/${encodeURIComponent(providerId)}/appointments?${query}`,
      undefined,
      signal,
    )
  }

  confirm(appointmentId: string): Promise<void> {
    return this.http.request(
      'POST',
      `/api/appointments/${encodeURIComponent(appointmentId)}/confirm`,
    )
  }

  cancel(appointmentId: string, reason: string): Promise<void> {
    return this.http.request(
      'POST',
      `/api/appointments/${encodeURIComponent(appointmentId)}/cancel`,
      { reason },
    )
  }
}
