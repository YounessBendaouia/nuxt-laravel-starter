/**
 * Public registration status (read-only).
 *
 * NOTE: This is purely a UX helper to hide/redirect away from the sign-up UI.
 * The backend is the only real enforcement point — it rejects every
 * registration attempt while registration is closed.
 */
export function useRegistrationStatus() {
  const client = useSanctumClient()
  const isRegistrationEnabled = useState<boolean | null>('registration-enabled', () => null)
  const showRegistrationNotice = useState<boolean>('registration-show-notice', () => false)

  async function fetchRegistrationStatus(): Promise<boolean> {
    try {
      const response = await client<{ enabled: boolean, show_notice?: boolean }>('/api/registration-status')
      isRegistrationEnabled.value = response?.enabled === true
      showRegistrationNotice.value = response?.enabled !== true && response?.show_notice === true
    } catch {
      // Fail closed: if the status cannot be determined, hide the sign-up UI silently.
      isRegistrationEnabled.value = false
      showRegistrationNotice.value = false
    }

    return isRegistrationEnabled.value
  }

  return {
    isRegistrationEnabled,
    showRegistrationNotice,
    fetchRegistrationStatus,
  }
}
