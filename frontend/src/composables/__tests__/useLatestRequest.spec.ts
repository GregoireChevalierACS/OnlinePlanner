import { describe, expect, it } from 'vitest'
import { deferred } from '@/__tests__/fakeGateway'
import { useLatestRequest } from '../useLatestRequest'

describe('useLatestRequest', () => {
  it('renvoie la valeur de la demande en cours', async () => {
    const run = useLatestRequest()

    await expect(run(async () => 42)).resolves.toEqual({ stale: false, value: 42 })
  })

  it("ignore une ancienne réponse arrivée après la nouvelle et annule l'ancienne demande", async () => {
    const run = useLatestRequest()
    const first = deferred<string>()
    const second = deferred<string>()
    let firstSignal: AbortSignal | undefined

    const firstRun = run((signal) => {
      firstSignal = signal
      return first.promise
    })
    const secondRun = run(() => second.promise)

    expect(firstSignal?.aborted).toBe(true)
    second.resolve('nouvelle')
    first.resolve('ancienne')

    await expect(secondRun).resolves.toEqual({ stale: false, value: 'nouvelle' })
    await expect(firstRun).resolves.toEqual({ stale: true })
  })

  it("ignore l'erreur d'une demande remplacée", async () => {
    const run = useLatestRequest()
    const first = deferred<string>()

    const firstRun = run(() => first.promise)
    const secondRun = run(async () => 'ok')
    first.reject(new DOMException('aborted', 'AbortError'))

    await expect(firstRun).resolves.toEqual({ stale: true })
    await expect(secondRun).resolves.toEqual({ stale: false, value: 'ok' })
  })

  it("propage l'erreur de la demande en cours", async () => {
    const run = useLatestRequest()

    await expect(run(async () => Promise.reject(new Error('boom')))).rejects.toThrow('boom')
  })
})
