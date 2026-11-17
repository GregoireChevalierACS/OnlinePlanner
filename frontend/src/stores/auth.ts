import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { toProblem } from '@/api/ApiError'
import { useAuthGateway } from '@/api/AuthGateway'
import type { Account, Problem } from '@/api/types'

/** Session du prestataire : restaurée au démarrage, puis tenue à jour par connexion / déconnexion. */
export const useAuthStore = defineStore('auth', () => {
  const gateway = useAuthGateway()

  const account = ref<Account | null>(null)
  const restored = ref(false)
  const submitting = ref(false)
  const problem = ref<Problem | null>(null)

  const isAuthenticated = computed(() => account.value !== null)

  let restoring: Promise<void> | null = null

  /** Interroge le serveur une seule fois, même si plusieurs écrans le demandent en même temps. */
  function restore(): Promise<void> {
    restoring ??= gateway
      .currentAccount()
      .then((current) => {
        account.value = current
      })
      .catch(() => {
        account.value = null
      })
      .finally(() => {
        restored.value = true
      })
    return restoring
  }

  async function submit(action: () => Promise<Account>): Promise<boolean> {
    submitting.value = true
    problem.value = null
    try {
      account.value = await action()
      restored.value = true
      return true
    } catch (error) {
      problem.value = toProblem(error)
      return false
    } finally {
      submitting.value = false
    }
  }

  const login = (email: string, password: string) => submit(() => gateway.login(email, password))
  const register = (email: string, password: string) =>
    submit(() => gateway.register(email, password))

  async function logout(): Promise<void> {
    try {
      await gateway.logout()
    } finally {
      account.value = null
    }
  }

  return {
    account,
    restored,
    submitting,
    problem,
    isAuthenticated,
    restore,
    login,
    register,
    logout,
  }
})
