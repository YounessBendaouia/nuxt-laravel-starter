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

  async function fetchRegistrationStatus(): Promise<boolean> {
    try {
      const response = await client<{ enabled: boolean }>('/api/registration-status')
      isRegistrationEnabled.value = response?.enabled === true
    } catch {
      // Fail closed: if the status cannot be determined, hide the sign-up UI.
      isRegistrationEnabled.value = false
    }

    return isRegistrationEnabled.value
  }

  return {
    isRegistrationEnabled,
    fetchRegistrationStatus,
  }
}
