<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner'

const route = useRoute()

useHead({
  title: () => (route.meta.title as string) || '',
  titleTemplate: (titleChunk) => {
    return titleChunk && titleChunk !== 'Nuxt Laravel Starter'
      ? `${titleChunk} · Nuxt Laravel Starter`
      : 'Nuxt Laravel Starter'
  },
})
</script>

<template>
  <div class="min-h-screen bg-background text-foreground antialiased selection:bg-primary/20 selection:text-primary">
    <NuxtRouteAnnouncer />
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
    <ClientOnly>
      <Toaster position="top-right" :rich-colors="true" :close-button="true" />
    </ClientOnly>
  </div>
</template>
