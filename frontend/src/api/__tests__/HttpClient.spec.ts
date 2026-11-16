import { describe, expect, it, vi } from 'vitest'
import { ApiError, toProblem } from '../ApiError'
import { HttpClient } from '../HttpClient'

const json = (status: number, body: unknown, statusText = '') =>
  new Response(JSON.stringify(body), {
    status,
    statusText,
    headers: { 'Content-Type': 'application/json' },
  })

describe('HttpClient', () => {
  it('envoie une requête GET et décode le JSON', async () => {
    const fetchFn = vi.fn<typeof fetch>().mockResolvedValue(json(200, [{ id: 1 }]))
    const signal = new AbortController().signal

    const result = await new HttpClient('https://api.test', fetchFn).request(
      'GET',
      '/x',
      undefined,
      signal,
    )

    expect(result).toEqual([{ id: 1 }])
    expect(fetchFn).toHaveBeenCalledWith('https://api.test/x', {
      method: 'GET',
      headers: { Accept: 'application/json' },
      body: undefined,
      signal,
    })
  })

  it('sérialise le corps des requêtes POST', async () => {
    const fetchFn = vi.fn<typeof fetch>().mockResolvedValue(json(201, { id: 'a1' }))

    await new HttpClient('', fetchFn).request('POST', '/x', { a: 1 })

    expect(fetchFn).toHaveBeenCalledWith('/x', {
      method: 'POST',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
      body: '{"a":1}',
      signal: undefined,
    })
  })

  it('renvoie undefined sur 204', async () => {
    const fetchFn = vi.fn<typeof fetch>().mockResolvedValue(new Response(null, { status: 204 }))

    await expect(new HttpClient('', fetchFn).request('POST', '/x')).resolves.toBeUndefined()
  })

  it('transforme un problem+json en ApiError', async () => {
    const problem = {
      status: 409,
      title: 'Conflict',
      detail: 'Créneau indisponible.',
      reasons: ['déjà réservé'],
    }
    const fetchFn = vi.fn<typeof fetch>().mockResolvedValue(json(409, problem))

    const error = await new HttpClient('', fetchFn).request('GET', '/x').catch((e: unknown) => e)

    expect(error).toBeInstanceOf(ApiError)
    expect((error as ApiError).problem).toEqual(problem)
    expect((error as ApiError).message).toBe('Créneau indisponible.')
    expect((error as ApiError).name).toBe('ApiError')
  })

  it.each([
    ['un corps non JSON', new Response('<html>', { status: 502, statusText: 'Bad Gateway' })],
    ["un JSON qui n'est pas un problem", json(502, { oops: true }, 'Bad Gateway')],
    ['un JSON null', json(502, null, 'Bad Gateway')],
    ['un statut non numérique', json(502, { status: '502', detail: 'x' }, 'Bad Gateway')],
    ['un détail non textuel', json(502, { status: 502, detail: 1 }, 'Bad Gateway')],
  ])('se replie sur un message générique pour %s', async (_case, response) => {
    const fetchFn = vi.fn<typeof fetch>().mockResolvedValue(response)

    const error = (await new HttpClient('', fetchFn)
      .request('GET', '/x')
      .catch((e: unknown) => e)) as ApiError

    expect(error.problem).toEqual({
      status: 502,
      title: 'Bad Gateway',
      detail: 'Une erreur inattendue est survenue.',
    })
  })

  it('utilise fetch global par défaut', async () => {
    const spy = vi.spyOn(globalThis, 'fetch').mockResolvedValue(json(200, { ok: true }))

    await expect(new HttpClient().request('GET', '/x')).resolves.toEqual({ ok: true })
    expect(spy).toHaveBeenCalledWith('/x', expect.objectContaining({ method: 'GET' }))
    spy.mockRestore()
  })
})

describe('toProblem', () => {
  it("reprend le problème d'une ApiError", () => {
    const problem = { status: 404, title: 'Not Found', detail: 'Introuvable.' }

    expect(toProblem(new ApiError(problem))).toBe(problem)
  })

  it('traduit toute autre erreur en serveur injoignable', () => {
    expect(toProblem(new TypeError('Failed to fetch'))).toEqual({
      status: 0,
      title: 'Serveur injoignable',
      detail: 'Le serveur ne répond pas. Vérifiez votre connexion puis réessayez.',
    })
  })
})
