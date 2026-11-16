import type { Problem } from './types'

/** Erreur renvoyée par l'API, porteuse du « problem details » à afficher. */
export class ApiError extends Error {
  constructor(public readonly problem: Problem) {
    super(problem.detail)
    this.name = 'ApiError'
  }

  static async fromResponse(response: Response): Promise<ApiError> {
    const fallback: Problem = {
      status: response.status,
      title: response.statusText,
      detail: 'Une erreur inattendue est survenue.',
    }
    try {
      const body: unknown = await response.json()
      return new ApiError(isProblem(body) ? body : fallback)
    } catch {
      return new ApiError(fallback)
    }
  }
}

function isProblem(value: unknown): value is Problem {
  return (
    typeof value === 'object' &&
    value !== null &&
    typeof (value as Problem).status === 'number' &&
    typeof (value as Problem).detail === 'string'
  )
}

/** Repli défini pour toute erreur : l'interface affiche toujours un message compréhensible. */
export function toProblem(error: unknown): Problem {
  if (error instanceof ApiError) {
    return error.problem
  }
  return {
    status: 0,
    title: 'Serveur injoignable',
    detail: 'Le serveur ne répond pas. Vérifiez votre connexion puis réessayez.',
  }
}
