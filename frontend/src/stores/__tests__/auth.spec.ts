import { describe, expect, it } from 'vitest'
import { ApiError } from '@/api/ApiError'
import { account, fakeAuthGateway, fakeGateway, withGateway } from '@/__tests__/fakeGateway'
import { useAuthStore } from '../auth'

function setup(auth = fakeAuthGateway()) {
  return { auth, store: withGateway(fakeGateway(), () => useAuthStore(), auth).result }
}

describe('auth store', () => {
  it('restaure la session une seule fois', async () => {
    const { auth, store } = setup()
    auth.currentAccount.mockResolvedValue(account)

    await Promise.all([store.restore(), store.restore()])
    await store.restore()

    expect(auth.currentAccount).toHaveBeenCalledTimes(1)
    expect(store.account).toEqual(account)
    expect(store.isAuthenticated).toBe(true)
    expect(store.restored).toBe(true)
  })

  it('considère la session absente si le serveur ne répond pas', async () => {
    const { auth, store } = setup()
    auth.currentAccount.mockRejectedValue(new TypeError('Failed to fetch'))

    await store.restore()

    expect(store.account).toBeNull()
    expect(store.restored).toBe(true)
  })

  it('connecte le compte', async () => {
    const { auth, store } = setup()

    const pending = store.login('jane@example.com', 'secret')
    expect(store.submitting).toBe(true)

    await expect(pending).resolves.toBe(true)
    expect(auth.login).toHaveBeenCalledWith('jane@example.com', 'secret')
    expect(store.account).toEqual(account)
    expect(store.restored).toBe(true)
    expect(store.submitting).toBe(false)
  })

  it('inscrit puis connecte le compte', async () => {
    const { auth, store } = setup()

    await expect(store.register('jane@example.com', 'secret')).resolves.toBe(true)

    expect(auth.register).toHaveBeenCalledWith('jane@example.com', 'secret')
    expect(store.isAuthenticated).toBe(true)
  })

  it("affiche le refus du serveur sans connecter l'utilisateur", async () => {
    const { auth, store } = setup()
    auth.login.mockRejectedValue(
      new ApiError({ status: 401, title: 'Unauthorized', detail: 'Identifiants incorrects.' }),
    )

    await expect(store.login('jane@example.com', 'faux')).resolves.toBe(false)

    expect(store.problem?.detail).toBe('Identifiants incorrects.')
    expect(store.account).toBeNull()
    expect(store.submitting).toBe(false)
  })

  it("oublie le compte même si l'appel de déconnexion échoue", async () => {
    const { auth, store } = setup()
    await store.login('jane@example.com', 'secret')
    auth.logout.mockRejectedValue(new TypeError('Failed to fetch'))

    await expect(store.logout()).rejects.toThrow('Failed to fetch')

    expect(store.account).toBeNull()
  })

  it('demande un lien de réinitialisation sans connecter personne', async () => {
    const { auth, store } = setup()

    await expect(store.requestPasswordReset('jane@example.com')).resolves.toBe(true)

    expect(auth.requestPasswordReset).toHaveBeenCalledWith('jane@example.com')
    expect(store.isAuthenticated).toBe(false)
  })

  it('change le mot de passe avec le jeton reçu', async () => {
    const { auth, store } = setup()

    await expect(store.resetPassword('jeton', 'nouveau mot de passe')).resolves.toBe(true)

    expect(auth.resetPassword).toHaveBeenCalledWith('jeton', 'nouveau mot de passe')
  })

  it('affiche un lien expiré puis efface le message', async () => {
    const { auth, store } = setup()
    auth.resetPassword.mockRejectedValue(
      new ApiError({ status: 422, title: 'Unprocessable', detail: 'Lien expiré.' }),
    )

    await expect(store.resetPassword('jeton', 'nouveau mot de passe')).resolves.toBe(false)
    expect(store.problem?.detail).toBe('Lien expiré.')

    store.clearProblem()
    expect(store.problem).toBeNull()
  })

  it('déconnecte', async () => {
    const { auth, store } = setup()
    await store.login('jane@example.com', 'secret')

    await store.logout()

    expect(auth.logout).toHaveBeenCalled()
    expect(store.isAuthenticated).toBe(false)
  })
})
