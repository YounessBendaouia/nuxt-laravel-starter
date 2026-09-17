<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { KeyRound, ArrowLeft, Loader2, CheckCircle2, AlertCircle, Eye, EyeOff } from '@lucide/vue'

definePageMeta({
  layout: 'auth',
  middleware: ['sanctum:guest'],
  title: 'Reset Password',
})

const route = useRoute()
const client = useSanctumClient()

const form = reactive({
  token: '',
  email: '',
  password: '',
  password_confirmation: '',
})

onMounted(() => {
  form.token = (route.query.token as string) || ''
  form.email = (route.query.email as string) || ''
})

const showPassword = ref(false)
const isLoading = ref(false)
const statusMessage = ref('')
const errorMessage = ref('')

async function onSubmit() {
  isLoading.value = true
  statusMessage.value = ''
  errorMessage.value = ''

  try {
    const response = await client<{ status?: string }>('/reset-password', {
      method: 'POST',
      body: form,
    })
    statusMessage.value = response?.status || 'Your password has been successfully reset.'
  } catch (err: any) {
    const data = err?.data || err?.response?._data
    errorMessage.value = data?.message || data?.errors?.password?.[0] || 'Failed to reset password. The link may have expired.'
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
          <KeyRound class="h-6 w-6" />
        </div>
        <CardTitle class="text-2xl font-bold tracking-tight">Set new password</CardTitle>
        <CardDescription class="text-sm text-muted-foreground">
          Create a new, strong password to secure your account
        </CardDescription>
      </CardHeader>

      <CardContent>
        <!-- Success Alert -->
        <div v-if="statusMessage" class="space-y-4">
          <Alert class="border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
            <CheckCircle2 class="h-4 w-4" />
            <AlertDescription class="font-medium text-xs">
              {{ statusMessage }}
            </AlertDescription>
          </Alert>

          <Button as-child class="w-full">
            <NuxtLink to="/auth/login">Proceed to Sign In</NuxtLink>
          </Button>
        </div>

        <form v-else class="space-y-4" @submit.prevent="onSubmit">
          <!-- Error Alert -->
          <Alert v-if="errorMessage" variant="destructive" class="mb-4 border-destructive/30 bg-destructive/10">
            <AlertCircle class="h-4 w-4" />
            <AlertDescription class="font-medium text-xs">
              {{ errorMessage }}
            </AlertDescription>
          </Alert>

          <div class="space-y-2">
            <Label for="email">Email address</Label>
            <Input
              id="email"
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
            />
          </div>

          <div class="space-y-2">
            <Label for="password">New Password</Label>
            <div class="relative">
              <Input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                required
                autocomplete="new-password"
                class="pr-10"
              />
              <button
                type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors p-1"
                @click="showPassword = !showPassword"
              >
                <EyeOff v-if="showPassword" class="h-4 w-4" />
                <Eye v-else class="h-4 w-4" />
              </button>
            </div>
          </div>

          <div class="space-y-2">
            <Label for="password_confirmation">Confirm New Password</Label>
            <Input
              id="password_confirmation"
              v-model="form.password_confirmation"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              required
              autocomplete="new-password"
            />
          </div>

          <Button type="submit" class="w-full font-medium" :disabled="isLoading">
            <Loader2 v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" />
            {{ isLoading ? 'Resetting password...' : 'Reset password' }}
          </Button>
        </form>

        <div class="mt-6 pt-4 border-t border-border/50 text-center">
          <NuxtLink
            to="/auth/login"
            class="inline-flex items-center gap-1.5 text-xs text-muted-foreground hover:text-foreground transition-colors"
          >
            <ArrowLeft class="h-3.5 w-3.5" />
            Back to login
          </NuxtLink>
        </div>
      </CardContent>
    </Card>
  </div>
</template>
