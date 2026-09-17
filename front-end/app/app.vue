<script setup lang="ts">
import { computed } from 'vue'
import { ConfigProvider } from 'reka-ui'
import { Toaster } from '@/components/ui/sonner'

const route = useRoute()
const { locale, locales, t, te } = useI18n()

// Compute current direction from active locale
const currentLocale = computed(() => {
  return locales.value.find((l: any) =>
    typeof l === 'string' ? l === locale.value : l.code === locale.value
  ) as any
})

const dir = computed<'ltr' | 'rtl'>(() => {
  return (currentLocale.value?.dir as 'ltr' | 'rtl') || 'ltr'
})

// Resolve page title dynamically with i18n
const resolvedTitle = computed(() => {
  const metaTitle = route.meta.title as string | undefined
  if (!metaTitle) return ''
  const normalizedKey = `pages.${metaTitle.toLowerCase().replace(/[\s-]+/g, '_')}`
  if (te(normalizedKey)) return t(normalizedKey)
  if (te(metaTitle)) return t(metaTitle)
  return metaTitle
})

useHead({
  htmlAttrs: {
    lang: () => locale.value,
    dir: () => dir.value,
  },
  title: () => resolvedTitle.value,
  titleTemplate: (titleChunk) => {
    return titleChunk && titleChunk !== 'Nuxt Laravel Starter'
      ? `${titleChunk} · Nuxt Laravel Starter`
      : 'Nuxt Laravel Starter'
  },
})
</script>

<template>
  <ConfigProvider :dir="dir">
    <div
      :dir="dir"
      class="min-h-screen bg-background text-foreground antialiased selection:bg-primary/20 selection:text-primary"
    >
      <NuxtRouteAnnouncer />
      <NuxtLayout>
        <NuxtPage />
      </NuxtLayout>
      <ClientOnly>
        <Toaster position="top-right" :rich-colors="true" :close-button="true" />
      </ClientOnly>
    </div>
  </ConfigProvider>
</template>
