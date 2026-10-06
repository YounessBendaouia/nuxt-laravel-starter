/**
 * Redirects away from the register page when public registration is closed.
 * UX only — the backend independently rejects registration attempts.
 */
export default defineNuxtRouteMiddleware(async () => {
  const { fetchRegistrationStatus } = useRegistrationStatus()

  if (!(await fetchRegistrationStatus())) {
    return navigateTo({ path: '/auth/login', query: { registration: 'disabled' } })
  }
})
