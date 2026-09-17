<script setup lang="ts">
import ThemeToggle from '@/components/ThemeToggle.vue'
import LocaleToggle from '@/components/LocaleToggle.vue'
import { ShieldCheck } from '@lucide/vue'
</script>

<template>
  <div class="relative min-h-screen flex flex-col justify-between bg-background overflow-hidden selection:bg-primary/20 selection:text-primary">
    <!-- Decorative background glow and grid -->
    <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
      <div class="absolute -top-[40%] left-[50%] -translate-x-1/2 h-[700px] w-[1000px] rounded-full bg-gradient-to-b from-primary/10 via-primary/5 to-transparent blur-3xl opacity-70" />
      <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_40%,#000_70%,transparent_100%)]" />
    </div>

    <!-- Header bar -->
    <header class="w-full max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
      <NuxtLink to="/" class="flex items-center gap-2.5 font-semibold text-lg tracking-tight group">
        <div class="h-9 w-9 rounded-xl bg-primary flex items-center justify-center text-primary-foreground shadow-sm group-hover:scale-105 transition-transform">
          <ShieldCheck class="h-5 w-5" />
        </div>
        <span class="font-bold bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text">
          Nuxt <span class="text-primary font-black">Laravel</span>
        </span>
      </NuxtLink>

      <div class="flex items-center gap-2">
        <LocaleToggle />
        <ClientOnly>
          <ThemeToggle />
        </ClientOnly>
      </div>
    </header>

    <!-- Main Form Content -->
    <main class="flex-1 flex items-center justify-center p-4 md:p-8">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="w-full py-4 text-center text-xs text-muted-foreground border-t border-border/40">
      <p>{{ $t('auth.footer_copyright', { year: new Date().getFullYear() }) }}</p>
    </footer>
  </div>
</template>
