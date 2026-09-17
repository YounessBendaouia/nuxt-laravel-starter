<script setup lang="ts">
import type { Component } from "vue"
import { IconCirclePlusFilled, IconMail } from "@tabler/icons-vue"

import { Button } from '@/components/ui/button'
import {
  SidebarGroup,
  SidebarGroupContent,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar'

interface NavItem {
  title: string
  url: string
  icon?: Component
}

defineProps<{
  items: NavItem[]
}>()

const route = useRoute()
</script>

<template>
  <SidebarGroup>
    <SidebarGroupContent class="flex flex-col gap-2">
      <SidebarMenu>
        <SidebarMenuItem class="flex items-center gap-2">
          <SidebarMenuButton
            :tooltip="$t('nav.quick_create')"
            class="bg-primary text-primary-foreground hover:bg-primary/90 hover:text-primary-foreground active:bg-primary/90 active:text-primary-foreground min-w-8 duration-200 ease-linear"
          >
            <IconCirclePlusFilled />
            <span>{{ $t('nav.quick_create') }}</span>
          </SidebarMenuButton>
          <Button
            size="icon"
            class="size-8 group-data-[collapsible=icon]:opacity-0"
            variant="outline"
          >
            <IconMail />
            <span class="sr-only">{{ $t('nav.inbox') }}</span>
          </Button>
        </SidebarMenuItem>
      </SidebarMenu>
      <SidebarMenu>
        <SidebarMenuItem v-for="item in items" :key="item.title">
          <SidebarMenuButton
            :tooltip="item.title"
            :is-active="route.path === item.url"
            as-child
          >
            <NuxtLink :to="item.url">
              <component :is="item.icon" v-if="item.icon" />
              <span>{{ item.title }}</span>
            </NuxtLink>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarGroupContent>
  </SidebarGroup>
</template>
