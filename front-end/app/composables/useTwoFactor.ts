import { ref } from 'vue'

export function useTwoFactor() {
  const client = useSanctumClient()
  const sanctumAuth = useSanctumAuth()

  const qrCode = ref<string>('')
  const recoveryCodes = ref<string[]>([])
  const isEnabling = ref(false)
  const isConfirming = ref(false)
  const isDisabling = ref(false)
  const isRegenerating = ref(false)
  const isChallenging = ref(false)
  const requiresPasswordConfirmation = ref(false)
  const errors = ref<Record<string, string[]>>({})

  function clearErrors() {
    errors.value = {}
  }

  function parseError(error: any) {
    const data = error?.data || error?.response?._data
    const status = error?.statusCode || error?.status || error?.response?.status

    if (status === 423) {
      requiresPasswordConfirmation.value = true
      errors.value = {
        general: ['Password confirmation required to modify two-factor authentication settings.'],
      }
    } else if (status === 422 && data?.errors) {
      errors.value = data.errors
    } else {
      errors.value = {
        general: [data?.message || error?.message || 'An error occurred. Please try again.'],
      }
    }
  }

  async function fetchQrCode() {
    try {
      const response = await client<{ svg: string }>('/user/two-factor-qr-code')
      qrCode.value = response?.svg || ''
    } catch (e: any) {
      parseError(e)
    }
  }

  async function fetchRecoveryCodes() {
    try {
      const response = await client<string[]>('/user/two-factor-recovery-codes')
      recoveryCodes.value = response || []
    } catch (e: any) {
      parseError(e)
    }
  }

  async function enable() {
    isEnabling.value = true
    clearErrors()
    try {
      await client('/user/two-factor-authentication', { method: 'POST' })
      await fetchQrCode()
      await fetchRecoveryCodes()
      return true
    } catch (e: any) {
      parseError(e)
      return false
    } finally {
      isEnabling.value = false
    }
  }

  async function confirm(code: string) {
    isConfirming.value = true
    clearErrors()
    try {
      await client('/user/confirmed-two-factor-authentication', {
        method: 'POST',
        body: { code },
      })
      await sanctumAuth.refreshIdentity()
      return true
    } catch (e: any) {
      parseError(e)
      return false
    } finally {
      isConfirming.value = false
    }
  }

  async function disable() {
    isDisabling.value = true
    clearErrors()
    try {
      await client('/user/two-factor-authentication', { method: 'DELETE' })
      qrCode.value = ''
      recoveryCodes.value = []
      await sanctumAuth.refreshIdentity()
      return true
    } catch (e: any) {
      parseError(e)
      return false
    } finally {
      isDisabling.value = false
    }
  }

  async function regenerateRecoveryCodes() {
    isRegenerating.value = true
    clearErrors()
    try {
      await client('/user/two-factor-recovery-codes', { method: 'POST' })
      await fetchRecoveryCodes()
      return true
    } catch (e: any) {
      parseError(e)
      return false
    } finally {
      isRegenerating.value = false
    }
  }

  async function confirmPassword(password: string) {
    clearErrors()
    try {
      await client('/user/confirm-password', {
        method: 'POST',
        body: { password },
      })
      requiresPasswordConfirmation.value = false
      return true
    } catch (e: any) {
      parseError(e)
      return false
    }
  }

  async function submitChallenge(payload: { code?: string; recovery_code?: string }) {
    isChallenging.value = true
    clearErrors()
    try {
      await client('/two-factor-challenge', {
        method: 'POST',
        body: payload,
      })
      await sanctumAuth.refreshIdentity()
      await navigateTo('/dashboard')
      return true
    } catch (e: any) {
      parseError(e)
      return false
    } finally {
      isChallenging.value = false
    }
  }

  return {
    qrCode,
    recoveryCodes,
    isEnabling,
    isConfirming,
    isDisabling,
    isRegenerating,
    isChallenging,
    requiresPasswordConfirmation,
    errors,
    clearErrors,
    enable,
    confirm,
    disable,
    fetchQrCode,
    fetchRecoveryCodes,
    regenerateRecoveryCodes,
    confirmPassword,
    submitChallenge,
  }
}
