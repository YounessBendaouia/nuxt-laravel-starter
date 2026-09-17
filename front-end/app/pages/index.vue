<script setup lang="ts">
definePageMeta({
  title: 'Welcome',
})

const { isAuthenticated } = useSanctumAuth()

// Automatically redirect to /dashboard if logged in, or /auth/login if guest
if (import.meta.client || import.meta.server) {
  if (isAuthenticated.value) {
    await navigateTo('/dashboard')
  } else {
    await navigateTo('/auth/login')
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center">
    <div class="flex flex-col items-center gap-2">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent" />
      <p class="text-sm text-muted-foreground">Redirecting...</p>
    </div>
  </div>
</template>
