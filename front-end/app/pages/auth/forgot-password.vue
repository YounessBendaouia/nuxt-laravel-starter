<script setup lang="ts">
import { ref } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Mail, ArrowLeft, Loader2, CheckCircle2, AlertCircle } from '@lucide/vue'

definePageMeta({
  layout: 'auth',
  middleware: ['sanctum:guest'],
  title: 'Forgot Password',
})

const { t } = useI18n()
const client = useSanctumClient()
const email = ref('')
const isLoading = ref(false)
const statusMessage = ref('')
const errorMessage = ref('')

async function onSubmit() {
  isLoading.value = true
  statusMessage.value = ''
  errorMessage.value = ''

  try {
    const response = await client<{ status?: string }>('/forgot-password', {
      method: 'POST',
      body: { email: email.value },
    })
    statusMessage.value = response?.status || t('auth.reset_link_sent')
  } catch (err: any) {
    const data = err?.data || err?.response?._data
    errorMessage.value = data?.message || data?.errors?.email?.[0] || t('common.error_occurred')
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="w-full max-w-md animate-in fade-in-50 zoom-in-95 duration-200">
    <Card class="border-border/60 shadow-xl backdrop-blur-sm bg-card/95">
      <CardHeader class="space-y-1.5 text-center pb-6">
        <div class="mx-auto h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-2">
          <Mail class="h-6 w-6" />
        </div>
        <CardTitle class="text-2xl font-bold tracking-tight">{{ t('auth.forgot_password_title') }}</CardTitle>
        <CardDescription class="text-sm text-muted-foreground">
          {{ t('auth.forgot_password_description') }}
        </CardDescription>
      </CardHeader>

      <CardContent>
        <!-- Success Alert -->
        <Alert v-if="statusMessage" class="mb-5 border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
          <CheckCircle2 class="h-4 w-4" />
          <AlertDescription class="font-medium text-xs">
            {{ statusMessage }}
          </AlertDescription>
        </Alert>

        <!-- Error Alert -->
        <Alert v-if="errorMessage" variant="destructive" class="mb-5 border-destructive/30 bg-destructive/10">
          <AlertCircle class="h-4 w-4" />
          <AlertDescription class="font-medium text-xs">
            {{ errorMessage }}
          </AlertDescription>
        </Alert>

        <form class="space-y-4" @submit.prevent="onSubmit">
          <div class="space-y-2">
            <Label for="email">{{ t('auth.email') }}</Label>
            <Input
              id="email"
              v-model="email"
              type="email"
              :placeholder="t('auth.email_placeholder')"
              required
              autocomplete="email"
            />
          </div>

          <Button type="submit" class="w-full font-medium" :disabled="isLoading">
            <Loader2 v-if="isLoading" class="me-2 h-4 w-4 animate-spin" />
            {{ isLoading ? t('common.loading') : t('auth.send_reset_link') }}
          </Button>
        </form>

        <div class="mt-6 pt-4 border-t border-border/50 text-center">
          <NuxtLink
            to="/auth/login"
            class="inline-flex items-center gap-1.5 text-xs text-muted-foreground hover:text-foreground transition-colors"
          >
            <ArrowLeft class="h-3.5 w-3.5 rtl:rotate-180" />
            {{ t('auth.back_to_login') }}
          </NuxtLink>
        </div>
      </CardContent>
    </Card>
  </div>
</template>
