<script setup lang="ts">
import { ref } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { KeyRound, ShieldAlert, Loader2, ArrowLeft, LifeBuoy } from '@lucide/vue'

definePageMeta({
  layout: 'auth',
  middleware: ['sanctum:guest'],
  title: 'Two-Factor Authentication',
})

const { t } = useI18n()
const { submitChallenge, isChallenging, errors } = useTwoFactor()

const isUsingRecoveryCode = ref(false)
const code = ref('')
const recoveryCode = ref('')

async function onSubmit() {
  if (isUsingRecoveryCode.value) {
    await submitChallenge({ recovery_code: recoveryCode.value })
  } else {
    await submitChallenge({ code: code.value })
  }
}
</script>

<template>
  <div class="w-full max-w-md animate-in fade-in-50 zoom-in-95 duration-200">
    <Card class="border-border/60 shadow-xl backdrop-blur-sm bg-card/95">
      <CardHeader class="space-y-1.5 text-center pb-6">
        <div class="mx-auto h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-2">
          <KeyRound v-if="!isUsingRecoveryCode" class="h-6 w-6" />
          <LifeBuoy v-else class="h-6 w-6" />
        </div>
        <CardTitle class="text-2xl font-bold tracking-tight">{{ t('auth.two_factor_title') }}</CardTitle>
        <CardDescription class="text-sm text-muted-foreground">
          {{ t('auth.two_factor_description') }}
        </CardDescription>
      </CardHeader>

      <CardContent>
        <!-- Error alert -->
        <Alert v-if="errors.general || errors.code || errors.recovery_code" variant="destructive" class="mb-5 border-destructive/30 bg-destructive/10">
          <ShieldAlert class="h-4 w-4" />
          <AlertDescription class="font-medium text-xs">
            {{ errors.code?.[0] || errors.recovery_code?.[0] || errors.general?.[0] }}
          </AlertDescription>
        </Alert>

        <form class="space-y-5" @submit.prevent="onSubmit">
          <!-- 6-digit code input -->
          <div v-if="!isUsingRecoveryCode" class="space-y-2">
            <Label for="code" class="text-center block text-sm font-medium">{{ t('auth.code') }}</Label>
            <Input
              id="code"
              v-model="code"
              type="text"
              inputmode="numeric"
              maxlength="6"
              placeholder="123456"
              class="text-center text-2xl tracking-[0.4em] font-mono h-12 font-bold"
              required
              autofocus
            />
          </div>

          <!-- Emergency recovery code input -->
          <div v-else class="space-y-2">
            <Label for="recoveryCode">{{ t('auth.recovery_code') }}</Label>
            <Input
              id="recoveryCode"
              v-model="recoveryCode"
              type="text"
              placeholder="abcd-efgh-ijkl-mnop"
              class="font-mono text-center h-11"
              required
              autofocus
            />
          </div>

          <!-- Submit Button -->
          <Button type="submit" class="w-full font-medium" :disabled="isChallenging">
            <Loader2 v-if="isChallenging" class="me-2 h-4 w-4 animate-spin" />
            {{ isChallenging ? t('auth.verifying') : t('auth.verify') }}
          </Button>

          <!-- Toggle between 2FA TOTP and Recovery code -->
          <div class="pt-2 text-center">
            <button
              type="button"
              class="text-xs text-muted-foreground hover:text-primary transition-colors underline-offset-4 hover:underline inline-flex items-center gap-1.5"
              @click="isUsingRecoveryCode = !isUsingRecoveryCode; code = ''; recoveryCode = ''"
            >
              <span>{{ !isUsingRecoveryCode ? t('auth.use_recovery_code') : t('auth.use_auth_code') }}</span>
            </button>
          </div>
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
