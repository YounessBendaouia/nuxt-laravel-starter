<script setup lang="ts">
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import { SidebarTrigger } from '@/components/ui/sidebar'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb'
import ThemeToggle from '@/components/ThemeToggle.vue'

const route = useRoute()

const breadcrumbs = computed(() => {
  const parts = route.path.split('/').filter(Boolean)
  if (parts.length === 0 || (parts.length === 1 && parts[0] === 'dashboard')) {
    return [{ name: 'Dashboard', path: '/dashboard', current: true }]
  }
  return parts.map((part, index) => {
    const path = '/' + parts.slice(0, index + 1).join('/')
    const name = part.charAt(0).toUpperCase() + part.slice(1)
    const current = index === parts.length - 1
    return { name, path, current }
  })
})
</script>

<template>
  <header class="flex h-(--header-height) shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-(--header-height)">
    <div class="flex w-full items-center gap-1 px-4 lg:gap-2 lg:px-6">
      <SidebarTrigger class="-ml-1" />
      <Separator
        orientation="vertical"
        class="mx-2 data-[orientation=vertical]:h-4"
      />
      <Breadcrumb class="hidden sm:block">
        <BreadcrumbList>
          <template v-for="(crumb, idx) in breadcrumbs" :key="crumb.path">
            <BreadcrumbItem>
              <BreadcrumbPage v-if="crumb.current" class="font-medium">
                {{ crumb.name }}
              </BreadcrumbPage>
              <BreadcrumbLink v-else as-child>
                <NuxtLink :to="crumb.path">{{ crumb.name }}</NuxtLink>
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator v-if="idx < breadcrumbs.length - 1" />
          </template>
        </BreadcrumbList>
      </Breadcrumb>
      <h1 class="text-base font-medium sm:hidden">
        {{ breadcrumbs[breadcrumbs.length - 1]?.name || 'Dashboard' }}
      </h1>
      <div class="ml-auto flex items-center gap-2">
        <ThemeToggle />
        <Button variant="ghost" as-child size="sm" class="hidden sm:flex">
          <a
            href="https://github.com/unovue/shadcn-vue"
            rel="noopener noreferrer"
            target="_blank"
            class="dark:text-foreground text-xs"
          >
            shadcn-vue
          </a>
        </Button>
      </div>
    </div>
  </header>
</template>
