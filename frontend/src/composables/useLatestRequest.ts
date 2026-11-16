export type LatestResult<T> = { stale: false; value: T } | { stale: true }

/**
 * Ne garde que la réponse de la dernière demande : la précédente est annulée (AbortController)
 * et, si elle répond quand même, sa réponse est ignorée. Évite qu'un ancien résultat
 * écrase le nouveau quand l'utilisateur change vite de jour.
 */
export function useLatestRequest() {
  let current: AbortController | null = null

  return async function run<T>(
    task: (signal: AbortSignal) => Promise<T>,
  ): Promise<LatestResult<T>> {
    current?.abort()
    const controller = new AbortController()
    current = controller

    try {
      const value = await task(controller.signal)
      return current === controller ? { stale: false, value } : { stale: true }
    } catch (error) {
      if (current !== controller) {
        return { stale: true }
      }
      throw error
    }
  }
}
