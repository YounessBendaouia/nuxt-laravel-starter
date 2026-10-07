/**
 * Redirects away from the register page when public registration is closed.
 * The "registration closed" notice query is only added when the backend says
 * the notice should be shown; otherwise the redirect is silent.
 * UX only — the backend independently rejects registration attempts.
 */
export default defineNuxtRouteMiddleware(async () => {
  const { fetchRegistrationStatus, showRegistrationNotice } = useRegistrationStatus()

  if (!(await fetchRegistrationStatus())) {
    return navigateTo(
      showRegistrationNotice.value
        ? { path: '/auth/login', query: { registration: 'disabled' } }
        : { path: '/auth/login' },
    )
  }
})
