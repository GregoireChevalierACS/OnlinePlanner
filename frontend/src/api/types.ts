/** Contrats JSON de l'API (voir docs/api.md). */

export interface Slot {
  startsAt: string
  endsAt: string
}

export type AppointmentStatus = 'pending' | 'confirmed' | 'cancelled' | 'completed'

export interface Appointment {
  id: string
  providerId: string
  customerName: string
  customerEmail: string
  startsAt: string
  endsAt: string
  status: AppointmentStatus
}

export interface OpeningHours {
  weekday: number
  opensAt: string
  closesAt: string
}

export interface NewProvider {
  name: string
  openingHours: OpeningHours[]
  slotDurationMinutes: number
  bufferMinutes: number
  minimumNoticeMinutes: number
}

export interface NewAppointment {
  startsAt: string
  customerName: string
  customerEmail: string
}

/** Réponse d'erreur de l'API (RFC 9457). */
export interface Problem {
  status: number
  title: string
  detail: string
  reasons?: string[]
  violations?: { field: string; message: string }[]
}
