import { ApiError } from './ApiError'

type Method = 'GET' | 'POST'

/** Enveloppe minimale de fetch : JSON en entrée et en sortie, erreurs en ApiError. */
export class HttpClient {
  constructor(
    private readonly baseUrl: string = '',
    private readonly fetchFn: typeof fetch = (input, init) => fetch(input, init),
  ) {}

  async request<T>(method: Method, path: string, body?: unknown, signal?: AbortSignal): Promise<T> {
    const headers: Record<string, string> = { Accept: 'application/json' }
    if (body !== undefined) {
      headers['Content-Type'] = 'application/json'
    }

    const response = await this.fetchFn(this.baseUrl + path, {
      method,
      headers,
      body: body === undefined ? undefined : JSON.stringify(body),
      signal,
    })

    if (!response.ok) {
      throw await ApiError.fromResponse(response)
    }
    if (response.status === 204) {
      return undefined as T
    }
    return (await response.json()) as T
  }
}
