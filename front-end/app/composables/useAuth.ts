import { ref } from 'vue'

export interface User {
  id: number
  name: string
  email: string
  avatar?: string | null
  email_verified_at?: string | null
  two_factor_confirmed_at?: string | null
  two_factor_enabled?: boolean
  created_at?: string
  updated_at?: string
}

export function useAuth() {
  const sanctumAuth = useSanctumAuth<User>()
  const client = useSanctumClient()
  const router = useRouter()

  const isLoading = ref(false)
  const errors = ref<Record<string, string[]>>({})

  function clearErrors() {
    errors.value = {}
  }

  function parseError(error: any) {
    const data = error?.data || error?.response?._data
    const status = error?.statusCode || error?.status || error?.response?.status

    if (status === 422 && data?.errors) {
      errors.value = data.errors
    } else if (status === 401) {
      errors.value = {
        general: [data?.message || 'Invalid email or password.'],
        email: ['Invalid credentials'],
      }
    } else if (status === 429) {
      errors.value = {
        general: [data?.message || 'Too many attempts. Please wait a moment and try again.'],
      }
    } else {
      errors.value = {
        general: [data?.message || error?.message || 'An unexpected error occurred. Please try again.'],
      }
    }
  }

  async function handleLogin(credentials: {
    email: string
    password: string
    remember?: boolean
  }) {
    isLoading.value = true
    clearErrors()

    try {
      // Direct call through sanctum client to capture 2FA response correctly
      const response = await client<any>('/api/login', {
        method: 'POST',
        body: credentials,
      })

      if (response?.two_factor) {
        // Two-factor authentication challenge required
        await navigateTo('/auth/two-factor-challenge')
        return { two_factor: true }
      }

      // Populate user identity in sanctum state
      await sanctumAuth.refreshIdentity()
      await navigateTo('/dashboard')
      return { two_factor: false, success: true }
    } catch (error: any) {
      parseError(error)
      return { success: false, error }
    } finally {
      isLoading.value = false
    }
  }

  async function handleRegister(data: {
    name: string
    email: string
    password: string
    password_confirmation: string
  }) {
    isLoading.value = true
    clearErrors()

    try {
      await client('/api/register', {
        method: 'POST',
        body: data,
      })

      // Authenticate and refresh identity
      await sanctumAuth.refreshIdentity()
      await navigateTo('/dashboard')
      return { success: true }
    } catch (error: any) {
      parseError(error)
      return { success: false, error }
    } finally {
      isLoading.value = false
    }
  }

  async function handleLogout() {
    isLoading.value = true
    try {
      await sanctumAuth.logout()
    } catch (e) {
      // Fallback redirect even if logout endpoint throws
    } finally {
      isLoading.value = false
      await navigateTo('/auth/login')
    }
  }

  return {
    user: sanctumAuth.user,
    isAuthenticated: sanctumAuth.isAuthenticated,
    refreshIdentity: sanctumAuth.refreshIdentity,
    isLoading,
    errors,
    clearErrors,
    handleLogin,
    handleRegister,
    handleLogout,
  }
}
