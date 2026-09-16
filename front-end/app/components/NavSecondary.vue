<script setup lang="ts">
import type { Component } from "vue"

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
    <SidebarGroupContent>
      <SidebarMenu>
        <SidebarMenuItem
          v-for="item in items"
          :key="item.title"
        >
          <SidebarMenuButton
            :is-active="route.path === item.url"
            as-child
          >
            <NuxtLink v-if="item.url.startsWith('/')" :to="item.url">
              <component :is="item.icon" v-if="item.icon" />
              {{ item.title }}
            </NuxtLink>
            <a v-else :href="item.url">
              <component :is="item.icon" v-if="item.icon" />
              {{ item.title }}
            </a>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarGroupContent>
  </SidebarGroup>
</template>
