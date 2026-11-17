import type { RouteLocationNormalized, RouteLocationRaw } from 'vue-router'

interface Session {
  isAuthenticated: boolean
  restore(): Promise<void>
}

/**
 * Garde de navigation : les pages marquées `requiresAuth` renvoient vers la connexion,
 * en mémorisant la page demandée pour y revenir ensuite.
 */
export function authGuard(session: () => Session) {
  return async (to: RouteLocationNormalized): Promise<true | RouteLocationRaw> => {
    if (to.meta.requiresAuth !== true) {
      return true
    }
    const current = session()
    await current.restore()
    if (current.isAuthenticated) {
      return true
    }
    return { name: 'login', query: { redirect: to.fullPath } }
  }
}

/** N'accepte qu'un chemin interne comme destination après connexion (pas de redirection ouverte). */
export function safeRedirect(value: unknown): string {
  return typeof value === 'string' && value.startsWith('/') && !value.startsWith('//') ? value : '/'
}
