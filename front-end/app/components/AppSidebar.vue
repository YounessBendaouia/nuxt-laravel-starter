<script setup lang="ts">
import { computed } from 'vue'
import type { SidebarProps } from '@/components/ui/sidebar'
import {
  IconCamera,
  IconChartBar,
  IconDashboard,
  IconDatabase,
  IconFileAi,
  IconFileDescription,
  IconFolder,
  IconHelp,
  IconInnerShadowTop,
  IconListDetails,
  IconReport,
  IconSearch,
  IconSettings,
  IconUsers,
} from "@tabler/icons-vue"

import NavDocuments from '@/components/NavDocuments.vue'
import NavMain from '@/components/NavMain.vue'
import NavSecondary from '@/components/NavSecondary.vue'
import NavUser from '@/components/NavUser.vue'
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar'

const props = withDefaults(
  defineProps<{
    variant?: SidebarProps['variant']
    collapsible?: SidebarProps['collapsible']
  }>(),
  {
    variant: 'inset',
    collapsible: 'offcanvas',
  }
)

const { user } = useAuth()
const { locale, locales, t, te } = useI18n()

const currentLocale = computed(() => {
  return locales.value.find((l: any) =>
    typeof l === 'string' ? l === locale.value : l.code === locale.value
  ) as any
})

const isRtl = computed(() => currentLocale.value?.dir === 'rtl')

const currentUser = computed(() => ({
  name: user.value?.name || "shadcn",
  email: user.value?.email || "m@example.com",
  avatar: user.value?.avatar || "/avatars/shadcn.jpg",
}))

const data = computed(() => ({
  navMain: [
    {
      title: te('nav.dashboard') ? t('nav.dashboard') : "Dashboard",
      url: "/dashboard",
      icon: IconDashboard,
    },
    {
      title: te('nav.lifecycle') ? t('nav.lifecycle') : "Lifecycle",
      url: "/dashboard",
      icon: IconListDetails,
    },
    {
      title: te('nav.analytics') ? t('nav.analytics') : "Analytics",
      url: "/dashboard/analytics",
      icon: IconChartBar,
    },
    {
      title: te('nav.projects') ? t('nav.projects') : "Projects",
      url: "/dashboard",
      icon: IconFolder,
    },
    {
      title: te('nav.team') ? t('nav.team') : "Team",
      url: "/dashboard/users",
      icon: IconUsers,
    },
  ],
  navClouds: [
    {
      title: "Capture",
      icon: IconCamera,
      isActive: true,
      url: "#",
      items: [
        {
          title: "Active Proposals",
          url: "#",
        },
        {
          title: "Archived",
          url: "#",
        },
      ],
    },
    {
      title: "Proposal",
      icon: IconFileDescription,
      url: "#",
      items: [
        {
          title: "Active Proposals",
          url: "#",
        },
        {
          title: "Archived",
          url: "#",
        },
      ],
    },
    {
      title: "Prompts",
      icon: IconFileAi,
      url: "#",
      items: [
        {
          title: "Active Proposals",
          url: "#",
        },
        {
          title: "Archived",
          url: "#",
        },
      ],
    },
  ],
  navSecondary: [
    {
      title: te('nav.settings') ? t('nav.settings') : "Settings",
      url: "/dashboard/settings",
      icon: IconSettings,
    },
    {
      title: te('nav.get_help') ? t('nav.get_help') : "Get Help",
      url: "#",
      icon: IconHelp,
    },
    {
      title: te('nav.search') ? t('nav.search') : "Search",
      url: "#",
      icon: IconSearch,
    },
  ],
  documents: [
    {
      name: te('nav.data_library') ? t('nav.data_library') : "Data Library",
      url: "#",
      icon: IconDatabase,
    },
    {
      name: te('nav.reports') ? t('nav.reports') : "Reports",
      url: "#",
      icon: IconReport,
    },
    {
      name: te('nav.word_assistant') ? t('nav.word_assistant') : "Word Assistant",
      url: "#",
      icon: IconFileDescription,
    },
  ],
}))
</script>

<template>
  <Sidebar
    :variant="props.variant"
    :collapsible="props.collapsible"
    :side="isRtl ? 'right' : 'left'"
  >
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton
            as-child
            class="data-[slot=sidebar-menu-button]:!p-1.5"
          >
            <NuxtLink to="/dashboard">
              <IconInnerShadowTop class="!size-5" />
              <span class="text-base font-semibold">Acme Inc.</span>
            </NuxtLink>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>
    <SidebarContent>
      <NavMain :items="data.navMain" />
      <NavDocuments :items="data.documents" />
      <NavSecondary :items="data.navSecondary" class="mt-auto" />
    </SidebarContent>
    <SidebarFooter>
      <NavUser :user="currentUser" />
    </SidebarFooter>
  </Sidebar>
</template>
