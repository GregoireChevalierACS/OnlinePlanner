import { describe, expect, it, vi } from 'vitest'
import type { RouteLocationNormalized } from 'vue-router'
import { authGuard, safeRedirect } from '../authGuard'

const route = (fullPath: string, requiresAuth?: boolean) =>
  ({
    fullPath,
    meta: requiresAuth === undefined ? {} : { requiresAuth },
  }) as RouteLocationNormalized

function session(isAuthenticated: boolean) {
  return { isAuthenticated, restore: vi.fn<() => Promise<void>>().mockResolvedValue(undefined) }
}

describe('authGuard', () => {
  it('laisse passer les pages publiques sans interroger le serveur', async () => {
    const current = session(false)

    await expect(authGuard(() => current)(route('/p/1/reserver'))).resolves.toBe(true)
    await expect(authGuard(() => current)(route('/x', false))).resolves.toBe(true)
    expect(current.restore).not.toHaveBeenCalled()
  })

  it('laisse passer un prestataire connecté', async () => {
    const current = session(true)

    await expect(authGuard(() => current)(route('/p/1/agenda', true))).resolves.toBe(true)
    expect(current.restore).toHaveBeenCalled()
  })

  it('renvoie vers la connexion en mémorisant la page demandée', async () => {
    await expect(
      authGuard(() => session(false))(route('/p/1/agenda?from=x', true)),
    ).resolves.toEqual({ name: 'login', query: { redirect: '/p/1/agenda?from=x' } })
  })
})

describe('safeRedirect', () => {
  it.each([
    ['/p/1/agenda', '/p/1/agenda'],
    ['//site-malveillant.example', '/'],
    ['https://site-malveillant.example', '/'],
    [undefined, '/'],
    [['/a', '/b'], '/'],
  ])('%s → %s', (input, expected) => {
    expect(safeRedirect(input)).toBe(expected)
  })
})
