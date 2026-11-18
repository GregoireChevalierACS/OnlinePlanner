import { describe, expect, it, vi } from 'vitest'
import { createApp } from 'vue'
import { ApiError } from '../ApiError'
import { authGatewayKey, HttpAuthGateway, useAuthGateway } from '../AuthGateway'
import { HttpClient } from '../HttpClient'

const account = { id: 'acc-1', email: 'jane@example.com' }

function gatewayWith(response: Response) {
  const fetchFn = vi.fn<typeof fetch>().mockResolvedValue(response)
  return { gateway: new HttpAuthGateway(new HttpClient('', fetchFn)), fetchFn }
}

const json = (status: number, body: unknown) => new Response(JSON.stringify(body), { status })
const problem = (status: number) => json(status, { status, title: 'x', detail: 'x' })

describe('HttpAuthGateway', () => {
  it('renvoie le compte connecté', async () => {
    const { gateway, fetchFn } = gatewayWith(json(200, account))

    await expect(gateway.currentAccount()).resolves.toEqual(account)
    expect(fetchFn.mock.calls[0]?.[0]).toBe('/api/auth/me')
  })

  it('renvoie null sans session', async () => {
    await expect(gatewayWith(problem(401)).gateway.currentAccount()).resolves.toBeNull()
  })

  it('propage les autres erreurs', async () => {
    await expect(gatewayWith(problem(500)).gateway.currentAccount()).rejects.toBeInstanceOf(
      ApiError,
    )
  })

  it.each([
    ['login', '/api/auth/login'],
    ['register', '/api/auth/register'],
  ] as const)('%s envoie les identifiants', async (method, url) => {
    const { gateway, fetchFn } = gatewayWith(json(200, account))

    await expect(gateway[method]('jane@example.com', 'secret')).resolves.toEqual(account)

    const [calledUrl, init] = fetchFn.mock.calls[0] ?? []
    expect(calledUrl).toBe(url)
    expect(init?.method).toBe('POST')
    expect(init?.body).toBe('{"email":"jane@example.com","password":"secret"}')
  })

  it('demande un lien de réinitialisation', async () => {
    const { gateway, fetchFn } = gatewayWith(new Response(null, { status: 202 }))

    await expect(gateway.requestPasswordReset('jane@example.com')).resolves.toBeUndefined()

    const [url, init] = fetchFn.mock.calls[0] ?? []
    expect(url).toBe('/api/auth/password-reset')
    expect(init?.body).toBe('{"email":"jane@example.com"}')
  })

  it('change le mot de passe avec le jeton', async () => {
    const { gateway, fetchFn } = gatewayWith(new Response(null, { status: 204 }))

    await gateway.resetPassword('jeton', 'nouveau mot de passe')

    const [url, init] = fetchFn.mock.calls[0] ?? []
    expect(url).toBe('/api/auth/password-reset/confirm')
    expect(init?.body).toBe('{"token":"jeton","password":"nouveau mot de passe"}')
  })

  it('déconnecte', async () => {
    const { gateway, fetchFn } = gatewayWith(new Response(null, { status: 204 }))

    await gateway.logout()

    expect(fetchFn.mock.calls[0]?.[0]).toBe('/api/auth/logout')
    expect(fetchFn.mock.calls[0]?.[1]?.method).toBe('POST')
  })
})

describe('useAuthGateway', () => {
  it("renvoie le gateway fourni par l'application", () => {
    const app = createApp({})
    const gateway = gatewayWith(json(200, account)).gateway
    app.provide(authGatewayKey, gateway)

    expect(app.runWithContext(() => useAuthGateway())).toBe(gateway)
  })

  it("échoue explicitement si aucun gateway n'est fourni", () => {
    expect(() => createApp({}).runWithContext(() => useAuthGateway())).toThrow(
      'Aucun AuthGateway fourni',
    )
  })
})
