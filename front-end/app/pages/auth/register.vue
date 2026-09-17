<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Separator } from '@/components/ui/separator'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Eye, EyeOff, Loader2, AlertCircle, Check, X, UserPlus } from '@lucide/vue'

definePageMeta({
  layout: 'auth',
  middleware: ['sanctum:guest'],
  title: 'Register',
})

const { t } = useI18n()
const { handleRegister, isLoading, errors } = useAuth()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const showPassword = ref(false)

// Dynamic password requirement rules checklist
const passwordRules = computed(() => [
  { label: t('auth.rule_min_length'), met: form.password.length >= 8 },
  { label: t('auth.rule_cases'), met: /[a-z]/.test(form.password) && /[A-Z]/.test(form.password) },
  { label: t('auth.rule_number'), met: /\d/.test(form.password) },
  { label: t('auth.rule_special'), met: /[^A-Za-z0-9]/.test(form.password) },
  { label: t('auth.rule_match'), met: form.password.length > 0 && form.password === form.password_confirmation },
])

const isPasswordValid = computed(() => passwordRules.value.every(r => r.met))

async function onSubmit() {
  await handleRegister({
    name: form.name,
    email: form.email,
    password: form.password,
    password_confirmation: form.password_confirmation,
  })
}
</script>

<template>
  <div class="w-full max-w-md animate-in fade-in-50 zoom-in-95 duration-200">
    <Card class="border-border/60 shadow-xl backdrop-blur-sm bg-card/95">
      <CardHeader class="space-y-1.5 text-center pb-6">
        <CardTitle class="text-2xl font-bold tracking-tight">{{ t('auth.register_title') }}</CardTitle>
        <CardDescription class="text-sm text-muted-foreground">
          {{ t('auth.register_description') }}
        </CardDescription>
      </CardHeader>

      <CardContent>
        <!-- General error banner -->
        <Alert v-if="errors.general" variant="destructive" class="mb-5 border-destructive/30 bg-destructive/10">
          <AlertCircle class="h-4 w-4" />
          <AlertDescription class="font-medium text-xs">
            {{ errors.general[0] }}
          </AlertDescription>
        </Alert>

        <form class="space-y-4" @submit.prevent="onSubmit">
          <!-- Full Name -->
          <div class="space-y-2">
            <Label for="name">{{ t('auth.name') }}</Label>
            <Input
              id="name"
              v-model="form.name"
              type="text"
              :placeholder="t('auth.name_placeholder')"
              required
              autocomplete="name"
              :class="{ 'border-destructive focus-visible:ring-destructive': errors.name }"
            />
            <p v-if="errors.name" class="text-xs font-medium text-destructive mt-1">
              {{ errors.name[0] }}
            </p>
          </div>

          <!-- Email -->
          <div class="space-y-2">
            <Label for="email">{{ t('auth.email') }}</Label>
            <Input
              id="email"
              v-model="form.email"
              type="email"
              :placeholder="t('auth.email_placeholder')"
              required
              autocomplete="email"
              :class="{ 'border-destructive focus-visible:ring-destructive': errors.email }"
            />
            <p v-if="errors.email" class="text-xs font-medium text-destructive mt-1">
              {{ errors.email[0] }}
            </p>
          </div>

          <!-- Password -->
          <div class="space-y-2">
            <Label for="password">{{ t('auth.password') }}</Label>
            <div class="relative">
              <Input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                required
                autocomplete="new-password"
                class="pe-10"
                :class="{ 'border-destructive focus-visible:ring-destructive': errors.password }"
              />
              <button
                type="button"
                class="absolute end-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors p-1"
                @click="showPassword = !showPassword"
              >
                <EyeOff v-if="showPassword" class="h-4 w-4" />
                <Eye v-else class="h-4 w-4" />
                <span class="sr-only">{{ showPassword ? t('auth.hide_password') : t('auth.show_password') }}</span>
              </button>
            </div>
            <p v-if="errors.password" class="text-xs font-medium text-destructive mt-1">
              {{ errors.password[0] }}
            </p>
          </div>

          <!-- Confirm Password -->
          <div class="space-y-2">
            <Label for="password_confirmation">{{ t('auth.confirm_password') }}</Label>
            <Input
              id="password_confirmation"
              v-model="form.password_confirmation"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              required
              autocomplete="new-password"
            />
          </div>

          <!-- Password Requirements Checklist -->
          <div class="rounded-lg bg-muted/40 p-3 text-xs space-y-1.5 border border-border/40">
            <p class="font-medium text-muted-foreground pb-1">{{ t('auth.password_requirements') }}</p>
            <div
              v-for="(rule, index) in passwordRules"
              :key="index"
              class="flex items-center gap-2 transition-colors duration-150"
              :class="rule.met ? 'text-emerald-500 dark:text-emerald-400' : 'text-muted-foreground'"
            >
              <Check v-if="rule.met" class="h-3.5 w-3.5 shrink-0" />
              <X v-else class="h-3.5 w-3.5 shrink-0 text-muted-foreground/60" />
              <span>{{ rule.label }}</span>
            </div>
          </div>

          <!-- Submit Button -->
          <Button type="submit" class="w-full mt-2 font-medium" :disabled="isLoading || !isPasswordValid">
            <Loader2 v-if="isLoading" class="me-2 h-4 w-4 animate-spin" />
            <UserPlus v-else class="me-2 h-4 w-4" />
            {{ isLoading ? t('auth.creating_account') : t('auth.create_account') }}
          </Button>
        </form>

        <div class="relative my-6">
          <Separator />
          <div class="absolute inset-0 flex items-center justify-center">
            <span class="bg-card px-2 text-xs text-muted-foreground uppercase tracking-wider font-semibold">
              {{ t('common.or') }}
            </span>
          </div>
        </div>

        <p class="text-center text-xs text-muted-foreground">
          {{ t('auth.already_have_account') }}
          <NuxtLink to="/auth/login" class="font-semibold text-primary hover:underline ms-1">
            {{ t('auth.sign_in') }}
          </NuxtLink>
        </p>
      </CardContent>
    </Card>
  </div>
</template>
