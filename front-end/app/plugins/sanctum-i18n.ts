export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.hook('sanctum:request', (_app, context) => {
    const i18n = nuxtApp.$i18n as { locale?: { value: string } } | undefined
    const currentLocale = i18n?.locale?.value || 'en'

    if (context.options.headers instanceof Headers) {
      context.options.headers.set('Accept-Language', currentLocale)
    } else if (Array.isArray(context.options.headers)) {
      context.options.headers.push(['Accept-Language', currentLocale])
    } else {
      context.options.headers = {
        ...(context.options.headers as Record<string, string> | undefined),
        'Accept-Language': currentLocale,
      }
    }
  })
})
