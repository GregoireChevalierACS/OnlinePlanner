import type { InjectionKey } from 'vue'
import { inject } from 'vue'
import { ApiError } from './ApiError'
import type { HttpClient } from './HttpClient'
import type { Account } from './types'

/** Port : connexion, inscription et session du prestataire. */
export interface AuthGateway {
  /** Compte connecté, ou null si la session est absente ou expirée. */
  currentAccount(): Promise<Account | null>
  login(email: string, password: string): Promise<Account>
  register(email: string, password: string): Promise<Account>
  logout(): Promise<void>
  /** Envoie un lien par e-mail si l'adresse a un compte ; la réponse ne dit pas lequel des deux. */
  requestPasswordReset(email: string): Promise<void>
  resetPassword(token: string, password: string): Promise<void>
  verifyEmail(token: string): Promise<void>
  /** Renvoie le lien de vérification au compte connecté. */
  resendEmailVerification(): Promise<void>
}

export const authGatewayKey: InjectionKey<AuthGateway> = Symbol('AuthGateway')

export function useAuthGateway(): AuthGateway {
  const gateway = inject(authGatewayKey)
  if (gateway === undefined) {
    throw new Error('Aucun AuthGateway fourni : appeler app.provide(authGatewayKey, …).')
  }
  return gateway
}

/** Adapter HTTP : session par cookie HttpOnly posé par l'API. */
export class HttpAuthGateway implements AuthGateway {
  constructor(private readonly http: HttpClient) {}

  async currentAccount(): Promise<Account | null> {
    try {
      return await this.http.request<Account>('GET', '/api/auth/me')
    } catch (error) {
      if (error instanceof ApiError && error.problem.status === 401) {
        return null
      }
      throw error
    }
  }

  login(email: string, password: string): Promise<Account> {
    return this.http.request('POST', '/api/auth/login', { email, password })
  }

  register(email: string, password: string): Promise<Account> {
    return this.http.request('POST', '/api/auth/register', { email, password })
  }

  logout(): Promise<void> {
    return this.http.request('POST', '/api/auth/logout')
  }

  requestPasswordReset(email: string): Promise<void> {
    return this.http.request('POST', '/api/auth/password-reset', { email })
  }

  resetPassword(token: string, password: string): Promise<void> {
    return this.http.request('POST', '/api/auth/password-reset/confirm', { token, password })
  }

  verifyEmail(token: string): Promise<void> {
    return this.http.request('POST', '/api/auth/verify-email', { token })
  }

  resendEmailVerification(): Promise<void> {
    return this.http.request('POST', '/api/auth/verify-email/resend')
  }
}
