<script setup lang="ts">
import { ref, computed } from 'vue'
import AppSidebar from '@/components/AppSidebar.vue'
import SiteHeader from '@/components/SiteHeader.vue'
import { Button } from '@/components/ui/button'
import { AlertCircle, CheckCircle2, Loader2, X } from '@lucide/vue'
import {
  SidebarInset,
  SidebarProvider,
} from '@/components/ui/sidebar'

const route = useRoute()
const { user, refreshIdentity } = useAuth()
const client = useSanctumClient()

const isResending = ref(false)
const resendFeedback = ref('')
const bannerDismissed = ref(false)

const justVerified = computed(() => route.query.verified === '1')

onMounted(() => {
  if (justVerified.value) {
    refreshIdentity()
  }
})

async function resendVerification() {
  isResending.value = true
  resendFeedback.value = ''
  try {
    await client('/api/email/verification-notification', { method: 'POST' })
    resendFeedback.value = 'Verification email sent!'
    setTimeout(() => {
      resendFeedback.value = ''
    }, 4000)
  } catch (err: any) {
    resendFeedback.value = 'Failed to send'
    setTimeout(() => {
      resendFeedback.value = ''
    }, 4000)
  } finally {
    isResending.value = false
  }
}
</script>

<template>
  <SidebarProvider
    :style="{
      '--sidebar-width': 'calc(var(--spacing) * 72)',
      '--header-height': 'calc(var(--spacing) * 12)',
    }"
  >
    <AppSidebar variant="inset" />
    <SidebarInset>
      <SiteHeader />

      <!-- Just Verified Success Alert -->
      <div
        v-if="justVerified"
        class="bg-emerald-500/10 border-b border-emerald-500/20 px-4 py-2.5 text-xs flex items-center justify-between text-emerald-700 dark:text-emerald-300 transition-all"
      >
        <div class="flex items-center gap-2">
          <CheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
          <span class="font-medium">Success! Your email address has been verified.</span>
        </div>
      </div>

      <!-- Unverified Email Notification Banner -->
      <div
        v-else-if="user && !user.email_verified_at && !bannerDismissed"
        class="bg-amber-500/10 border-b border-amber-500/20 px-4 py-2 text-xs flex items-center justify-between text-amber-700 dark:text-amber-300 transition-all"
      >
        <div class="flex items-center gap-2">
          <AlertCircle class="h-4 w-4 shrink-0 text-amber-500" />
          <span>Your email address is unverified. Please check your inbox or spam folder.</span>
        </div>
        <div class="flex items-center gap-2">
          <span v-if="resendFeedback" class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
            {{ resendFeedback }}
          </span>
          <Button
            v-else
            size="sm"
            variant="outline"
            class="h-7 text-xs border-amber-500/30 hover:bg-amber-500/20"
            :disabled="isResending"
            @click="resendVerification"
          >
            <Loader2 v-if="isResending" class="h-3 w-3 animate-spin mr-1" />
            Resend link
          </Button>
          <button
            type="button"
            class="text-muted-foreground hover:text-foreground p-1 rounded transition-colors"
            title="Dismiss banner"
            @click="bannerDismissed = true"
          >
            <X class="h-3.5 w-3.5" />
          </button>
        </div>
      </div>

      <div class="flex flex-1 flex-col">
        <div class="@container/main flex flex-1 flex-col gap-2">
          <slot />
        </div>
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>
