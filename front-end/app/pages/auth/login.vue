<script setup lang="ts">
import { reactive, ref } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Separator } from '@/components/ui/separator'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Eye, EyeOff, Loader2, AlertCircle, LogIn, CheckCircle2 } from '@lucide/vue'

definePageMeta({
  layout: 'auth',
  middleware: ['sanctum:guest'],
  title: 'Log In',
})

const route = useRoute()
const { handleLogin, isLoading, errors } = useAuth()

const form = reactive({
  email: '',
  password: '',
  remember: false,
})

const showPassword = ref(false)

async function onSubmit() {
  await handleLogin({
    email: form.email,
    password: form.password,
    remember: form.remember,
  })
}
</script>

<template>
  <div class="w-full max-w-md animate-in fade-in-50 zoom-in-95 duration-200">
    <Card class="border-border/60 shadow-xl backdrop-blur-sm bg-card/95">
      <CardHeader class="space-y-1.5 text-center pb-6">
        <CardTitle class="text-2xl font-bold tracking-tight">Welcome back</CardTitle>
        <CardDescription class="text-sm text-muted-foreground">
          Enter your email and password to access your dashboard
        </CardDescription>
      </CardHeader>

      <CardContent>
        <!-- Email verified success alert -->
        <Alert v-if="route.query.verified === '1'" class="mb-5 border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
          <CheckCircle2 class="h-4 w-4" />
          <AlertDescription class="font-medium text-xs">
            Your email address has been successfully verified! You may now sign in.
          </AlertDescription>
        </Alert>

        <!-- Email verified invalid alert -->
        <Alert v-if="route.query.verified === 'invalid'" variant="destructive" class="mb-5 border-destructive/30 bg-destructive/10">
          <AlertCircle class="h-4 w-4" />
          <AlertDescription class="font-medium text-xs">
            The email verification link is invalid or has expired. Please sign in and request a new one.
          </AlertDescription>
        </Alert>

        <!-- Password reset success alert -->
        <Alert v-if="route.query.reset === '1'" class="mb-5 border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
          <CheckCircle2 class="h-4 w-4" />
          <AlertDescription class="font-medium text-xs">
            Your password was reset successfully. Please sign in with your new password.
          </AlertDescription>
        </Alert>

        <!-- General error notification banner -->
        <Alert v-if="errors.general" variant="destructive" class="mb-5 border-destructive/30 bg-destructive/10">
          <AlertCircle class="h-4 w-4" />
          <AlertDescription class="font-medium text-xs">
            {{ errors.general[0] }}
          </AlertDescription>
        </Alert>

        <form class="space-y-4" @submit.prevent="onSubmit">
          <!-- Email Field -->
          <div class="space-y-2">
            <Label for="email">Email address</Label>
            <Input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="name@example.com"
              required
              autocomplete="email"
              :class="{ 'border-destructive focus-visible:ring-destructive': errors.email }"
            />
            <p v-if="errors.email" class="text-xs font-medium text-destructive mt-1">
              {{ errors.email[0] }}
            </p>
          </div>

          <!-- Password Field -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <Label for="password">Password</Label>
              <NuxtLink
                to="/auth/forgot-password"
                class="text-xs font-medium text-muted-foreground hover:text-primary transition-colors underline-offset-4 hover:underline"
              >
                Forgot password?
              </NuxtLink>
            </div>
            <div class="relative">
              <Input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                required
                autocomplete="current-password"
                class="pr-10"
                :class="{ 'border-destructive focus-visible:ring-destructive': errors.password }"
              />
              <button
                type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors p-1"
                @click="showPassword = !showPassword"
              >
                <EyeOff v-if="showPassword" class="h-4 w-4" />
                <Eye v-else class="h-4 w-4" />
                <span class="sr-only">{{ showPassword ? 'Hide password' : 'Show password' }}</span>
              </button>
            </div>
            <p v-if="errors.password" class="text-xs font-medium text-destructive mt-1">
              {{ errors.password[0] }}
            </p>
          </div>

          <!-- Remember Me Checkbox -->
          <div class="flex items-center space-x-2 pt-1">
            <Checkbox
              id="remember"
              :checked="form.remember"
              @update:checked="(val: boolean) => form.remember = val"
            />
            <label
              for="remember"
              class="text-xs text-muted-foreground leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer select-none"
            >
              Remember me for 30 days
            </label>
          </div>

          <!-- Submit Button -->
          <Button type="submit" class="w-full mt-2 font-medium" :disabled="isLoading">
            <Loader2 v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" />
            <LogIn v-else class="mr-2 h-4 w-4" />
            {{ isLoading ? 'Signing in...' : 'Sign in' }}
          </Button>
        </form>

        <div class="relative my-6">
          <Separator />
          <div class="absolute inset-0 flex items-center justify-center">
            <span class="bg-card px-2 text-xs text-muted-foreground uppercase tracking-wider font-semibold">
              Or
            </span>
          </div>
        </div>

        <p class="text-center text-xs text-muted-foreground">
          Don't have an account?
          <NuxtLink to="/auth/register" class="font-semibold text-primary hover:underline ml-1">
            Create an account
          </NuxtLink>
        </p>
      </CardContent>
    </Card>
  </div>
</template>
