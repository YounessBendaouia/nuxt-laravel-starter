<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import {
  User as UserIcon,
  Lock,
  ShieldCheck,
  ShieldAlert,
  QrCode,
  Copy,
  RefreshCw,
  Loader2,
  CheckCircle2,
  AlertCircle,
  Key,
} from '@lucide/vue'
import { toast } from 'vue-sonner'

definePageMeta({
  layout: 'dashboard',
  middleware: ['sanctum:auth'],
  title: 'Settings',
})

const { user, refreshIdentity } = useAuth()
const client = useSanctumClient()
const twoFactor = useTwoFactor()

// Profile Form State
const profileForm = reactive({
  name: user.value?.name || '',
  email: user.value?.email || '',
  avatar: user.value?.avatar || '',
})

// Keep form synchronized when user data loads or changes
watch(
  user,
  (newUser) => {
    if (newUser) {
      profileForm.name = newUser.name || ''
      profileForm.email = newUser.email || ''
      profileForm.avatar = newUser.avatar || ''
    }
  },
  { immediate: true },
)

const isUpdatingProfile = ref(false)
const profileSuccess = ref('')
const profileError = ref('')

const isEmailVerified = computed(() => !!user.value?.email_verified_at)
const isResendingVerification = ref(false)
const resendVerificationText = ref('')

async function handleResendVerification() {
  isResendingVerification.value = true
  resendVerificationText.value = ''
  try {
    const res = await client<{ message?: string }>('/api/email/verification-notification', {
      method: 'POST',
    })
    const msg = res?.message || 'Verification email sent! Check your inbox or Mailpit.'
    toast.success('Verification Email Sent', {
      description: 'Check your inbox or Mailpit (http://localhost:8025) for your verification link.',
    })
    resendVerificationText.value = 'Sent!'
    setTimeout(() => {
      resendVerificationText.value = ''
    }, 4000)
  } catch (err: any) {
    const data = err?.data || err?.response?._data
    const msg = data?.message || 'Failed to send verification email.'
    toast.error('Unable to send email', { description: msg })
  } finally {
    isResendingVerification.value = false
  }
}

async function updateProfile() {
  isUpdatingProfile.value = true
  profileSuccess.value = ''
  profileError.value = ''
  try {
    const res = await client<{ message: string; user: any }>('/api/user/profile', {
      method: 'PUT',
      body: {
        name: profileForm.name,
        email: profileForm.email,
        ...(profileForm.avatar ? { avatar: profileForm.avatar } : {}),
      },
    })
    await refreshIdentity()
    const msg = res?.message || 'Profile updated successfully!'
    profileSuccess.value = msg
    toast.success('Profile Updated', {
      description: 'Your account details have been successfully saved.',
    })
  } catch (err: any) {
    const data = err?.data || err?.response?._data
    profileError.value = data?.message || data?.errors?.email?.[0] || 'Failed to update profile.'
    toast.error('Profile Update Failed', {
      description: profileError.value,
    })
  } finally {
    isUpdatingProfile.value = false
  }
}

// Password Form State
const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const isUpdatingPassword = ref(false)
const passwordSuccess = ref('')
const passwordError = ref('')

async function updatePassword() {
  isUpdatingPassword.value = true
  passwordSuccess.value = ''
  passwordError.value = ''
  try {
    const res = await client<{ message: string }>('/api/user/password', {
      method: 'PUT',
      body: passwordForm,
    })
    const msg = res?.message || 'Password updated successfully!'
    passwordSuccess.value = msg
    toast.success('Password Updated', {
      description: 'Your account password has been changed successfully.',
    })
    passwordForm.current_password = ''
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
  } catch (err: any) {
    const data = err?.data || err?.response?._data
    passwordError.value = data?.message || data?.errors?.current_password?.[0] || data?.errors?.password?.[0] || 'Failed to update password.'
    toast.error('Password Update Failed', {
      description: passwordError.value,
    })
  } finally {
    isUpdatingPassword.value = false
  }
}

// 2FA Management State
const confirmCode = ref('')
const confirmPasswordInput = ref('')
const twoFactorSuccess = ref('')
const twoFactorError = ref('')

const is2FAEnabled = computed(() => !!(user.value?.two_factor_enabled ?? user.value?.two_factor_confirmed_at))

async function handleEnable2FA() {
  twoFactorSuccess.value = ''
  twoFactorError.value = ''
  const success = await twoFactor.enable()
  if (success) {
    toast.info('Scan Authenticator Code', {
      description: 'Scan the QR code with your authenticator app to finish setup.',
    })
  }
}

async function handleConfirm2FA() {
  if (!confirmCode.value) return
  twoFactorSuccess.value = ''
  twoFactorError.value = ''
  const success = await twoFactor.confirm(confirmCode.value)
  if (success) {
    twoFactorSuccess.value = 'Two-factor authentication is now active.'
    toast.success('2FA Activated', {
      description: 'Two-factor authentication is now protecting your account.',
    })
    confirmCode.value = ''
    twoFactor.qrCode.value = ''
    await refreshIdentity()
  } else {
    twoFactorError.value = 'Invalid confirmation code. Please check your authenticator app and try again.'
    toast.error('Verification Failed', {
      description: twoFactorError.value,
    })
  }
}

async function handleCancelSetup() {
  await twoFactor.disable()
  confirmCode.value = ''
  twoFactor.qrCode.value = ''
  twoFactorSuccess.value = ''
  twoFactorError.value = ''
  await refreshIdentity()
  toast.info('2FA Setup Cancelled')
}

async function handleDisable2FA() {
  twoFactorSuccess.value = ''
  twoFactorError.value = ''
  const success = await twoFactor.disable()
  if (success) {
    twoFactorSuccess.value = 'Two-factor authentication has been disabled.'
    toast.success('2FA Disabled', {
      description: 'Two-factor authentication has been removed from your account.',
    })
    await refreshIdentity()
  } else {
    twoFactorError.value = 'Failed to disable two-factor authentication.'
    toast.error('Operation Failed', {
      description: twoFactorError.value,
    })
  }
}

async function handleConfirmPassword() {
  const success = await twoFactor.confirmPassword(confirmPasswordInput.value)
  if (success) {
    toast.success('Password Verified', {
      description: 'You can now configure two-factor authentication.',
    })
    confirmPasswordInput.value = ''
  } else {
    toast.error('Incorrect Password', {
      description: 'The password you entered does not match our records.',
    })
  }
}

function copyRecoveryCodes() {
  if (!twoFactor.recoveryCodes.value.length) return
  navigator.clipboard.writeText(twoFactor.recoveryCodes.value.join('\n'))
  toast.success('Recovery codes copied to clipboard!')
}
</script>

<template>
  <div class="flex flex-col gap-4 py-4 md:gap-6 md:py-6 px-4 lg:px-6">
    <div class="flex flex-col gap-1">
      <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Account Settings</h1>
      <p class="text-sm text-muted-foreground">
        Manage your profile details, password security, and two-factor authentication.
      </p>
    </div>

    <div class="max-w-4xl">
      <Tabs default-value="profile" class="space-y-6">
        <TabsList class="grid w-full grid-cols-3 max-w-md">
        <TabsTrigger value="profile" class="gap-2">
          <UserIcon class="h-4 w-4" />
          Profile
        </TabsTrigger>
        <TabsTrigger value="password" class="gap-2">
          <Lock class="h-4 w-4" />
          Password
        </TabsTrigger>
        <TabsTrigger value="security" class="gap-2">
          <ShieldCheck class="h-4 w-4" />
          Security (2FA)
        </TabsTrigger>
      </TabsList>

      <!-- TAB 1: Profile Information -->
      <TabsContent value="profile" class="space-y-6">
        <Card class="border-border/60 shadow-sm">
          <CardHeader>
            <CardTitle class="text-lg">Profile Information</CardTitle>
            <CardDescription class="text-xs">
              Update your account's profile information and public details.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <!-- Success Message Alert -->
            <Alert v-if="profileSuccess" class="mb-4 border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
              <CheckCircle2 class="h-4 w-4" />
              <AlertDescription class="text-xs font-medium">{{ profileSuccess }}</AlertDescription>
            </Alert>

            <!-- Error Message Alert -->
            <Alert v-if="profileError" variant="destructive" class="mb-4 border-destructive/30 bg-destructive/10">
              <AlertCircle class="h-4 w-4" />
              <AlertDescription class="text-xs font-medium">{{ profileError }}</AlertDescription>
            </Alert>

            <form class="space-y-4" @submit.prevent="updateProfile">
              <div class="space-y-2">
                <Label for="name">Full Name</Label>
                <Input id="name" v-model="profileForm.name" required />
              </div>

              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <Label for="email">Email Address</Label>
                    <Badge
                      :variant="isEmailVerified ? 'default' : 'outline'"
                      :class="isEmailVerified
                        ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/30 gap-1 text-[11px] font-medium'
                        : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30 gap-1 text-[11px] font-medium'"
                    >
                      <CheckCircle2 v-if="isEmailVerified" class="h-3 w-3" />
                      <AlertCircle v-else class="h-3 w-3" />
                      {{ isEmailVerified ? 'Verified' : 'Unverified' }}
                    </Badge>
                  </div>
                  <Button
                    v-if="!isEmailVerified"
                    type="button"
                    variant="ghost"
                    size="sm"
                    class="h-6 text-xs text-primary hover:underline px-1.5"
                    :disabled="isResendingVerification"
                    @click="handleResendVerification"
                  >
                    <Loader2 v-if="isResendingVerification" class="h-3 w-3 animate-spin mr-1" />
                    {{ resendVerificationText || 'Resend link' }}
                  </Button>
                </div>
                <Input id="email" v-model="profileForm.email" type="email" required />
              </div>

              <div class="space-y-2">
                <Label for="avatar">Avatar Image URL (Optional)</Label>
                <Input id="avatar" v-model="profileForm.avatar" placeholder="https://example.com/avatar.jpg" />
              </div>

              <Button type="submit" :disabled="isUpdatingProfile" class="font-medium">
                <Loader2 v-if="isUpdatingProfile" class="mr-2 h-4 w-4 animate-spin" />
                <CheckCircle2 v-else-if="profileSuccess" class="mr-2 h-4 w-4 text-emerald-500" />
                {{ isUpdatingProfile ? 'Saving changes...' : profileSuccess ? 'Changes Saved' : 'Save Profile Changes' }}
              </Button>
            </form>
          </CardContent>
        </Card>
      </TabsContent>

      <!-- TAB 2: Update Password -->
      <TabsContent value="password" class="space-y-6">
        <Card class="border-border/60 shadow-sm">
          <CardHeader>
            <CardTitle class="text-lg">Update Password</CardTitle>
            <CardDescription class="text-xs">
              Ensure your account is protected by a strong, random password.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <!-- Success Message Alert -->
            <Alert v-if="passwordSuccess" class="mb-4 border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
              <CheckCircle2 class="h-4 w-4" />
              <AlertDescription class="text-xs font-medium">{{ passwordSuccess }}</AlertDescription>
            </Alert>

            <!-- Error Message Alert -->
            <Alert v-if="passwordError" variant="destructive" class="mb-4 border-destructive/30 bg-destructive/10">
              <AlertCircle class="h-4 w-4" />
              <AlertDescription class="text-xs font-medium">{{ passwordError }}</AlertDescription>
            </Alert>

            <form class="space-y-4" @submit.prevent="updatePassword">
              <div class="space-y-2">
                <Label for="currentPassword">Current Password</Label>
                <Input id="currentPassword" v-model="passwordForm.current_password" type="password" required />
              </div>

              <div class="space-y-2">
                <Label for="newPassword">New Password</Label>
                <Input id="newPassword" v-model="passwordForm.password" type="password" required />
                <p class="text-[11px] text-muted-foreground">
                  Must be at least 8 characters and include uppercase, lowercase, numbers, and symbols.
                </p>
              </div>

              <div class="space-y-2">
                <Label for="confirmPassword">Confirm New Password</Label>
                <Input id="confirmPassword" v-model="passwordForm.password_confirmation" type="password" required />
              </div>

              <Button type="submit" :disabled="isUpdatingPassword" class="font-medium">
                <Loader2 v-if="isUpdatingPassword" class="mr-2 h-4 w-4 animate-spin" />
                <CheckCircle2 v-else-if="passwordSuccess" class="mr-2 h-4 w-4 text-emerald-500" />
                {{ isUpdatingPassword ? 'Updating...' : passwordSuccess ? 'Password Updated' : 'Update Password' }}
              </Button>
            </form>
          </CardContent>
        </Card>
      </TabsContent>

      <!-- TAB 3: Security & Two-Factor Authentication -->
      <TabsContent value="security" class="space-y-6">
        <!-- Locked Session (423) Password Confirmation Prompt -->
        <Card v-if="twoFactor.requiresPasswordConfirmation.value" class="border-amber-500/30 bg-amber-500/5 shadow-sm">
          <CardHeader>
            <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
              <Key class="h-5 w-5" />
              <CardTitle class="text-base">Confirm Password to Continue</CardTitle>
            </div>
            <CardDescription class="text-xs">
              For security reasons, please confirm your account password before configuring two-factor authentication.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form class="space-y-4" @submit.prevent="handleConfirmPassword">
              <div class="space-y-2">
                <Label for="secPassword">Current Password</Label>
                <Input id="secPassword" v-model="confirmPasswordInput" type="password" required />
              </div>
              <Button type="submit">Confirm Password</Button>
            </form>
          </CardContent>
        </Card>

        <!-- Main 2FA Card -->
        <Card class="border-border/60 shadow-sm">
          <CardHeader class="flex flex-row items-center justify-between">
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <CardTitle class="text-lg">Two-Factor Authentication (2FA)</CardTitle>
                <Badge
                  :variant="is2FAEnabled ? 'default' : 'secondary'"
                  :class="is2FAEnabled ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : ''"
                >
                  {{ is2FAEnabled ? 'Enabled' : 'Disabled' }}
                </Badge>
              </div>
              <CardDescription class="text-xs">
                Add an extra layer of security using Google Authenticator, 1Password, or Authy.
              </CardDescription>
            </div>
          </CardHeader>

          <CardContent class="space-y-6">
            <!-- 2FA Success Alert -->
            <Alert v-if="twoFactorSuccess" class="border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
              <CheckCircle2 class="h-4 w-4" />
              <AlertDescription class="text-xs font-medium">{{ twoFactorSuccess }}</AlertDescription>
            </Alert>

            <!-- 2FA Error Alert -->
            <Alert v-if="twoFactorError" variant="destructive" class="border-destructive/30 bg-destructive/10">
              <AlertCircle class="h-4 w-4" />
              <AlertDescription class="text-xs font-medium">{{ twoFactorError }}</AlertDescription>
            </Alert>

            <!-- 2FA is currently Disabled -->
            <div v-if="!is2FAEnabled && !twoFactor.qrCode.value" class="space-y-4">
              <p class="text-xs text-muted-foreground leading-relaxed">
                When two-factor authentication is enabled, you will be prompted for a secure, 6-digit code
                during login in addition to your password.
              </p>
              <Button
                :disabled="twoFactor.isEnabling.value"
                class="font-medium gap-2"
                @click="handleEnable2FA"
              >
                <Loader2 v-if="twoFactor.isEnabling.value" class="h-4 w-4 animate-spin" />
                <ShieldCheck v-else class="h-4 w-4" />
                Enable Two-Factor Authentication
              </Button>
            </div>

            <!-- 2FA QR Code Setup in Progress -->
            <div v-if="!is2FAEnabled && twoFactor.qrCode.value" class="space-y-6 p-4 rounded-xl border border-primary/20 bg-primary/5 animate-in fade-in-50 duration-200">
              <div>
                <h3 class="text-sm font-semibold text-foreground flex items-center gap-2">
                  <QrCode class="h-4 w-4 text-primary" />
                  Step 1: Scan this QR code
                </h3>
                <p class="text-xs text-muted-foreground mt-1">
                  Scan the QR code with your authenticator app (Google Authenticator, Authy, 1Password).
                </p>
              </div>

              <!-- QR Code SVG container -->
              <div class="p-4 bg-white rounded-lg inline-block shadow-sm" v-html="twoFactor.qrCode.value" />

              <div class="space-y-3 pt-2">
                <h3 class="text-sm font-semibold text-foreground">
                  Step 2: Enter the 6-digit code from your app
                </h3>
                <div class="flex items-center gap-3 max-w-sm">
                  <Input
                    v-model="confirmCode"
                    type="text"
                    placeholder="123456"
                    maxlength="6"
                    class="font-mono text-center tracking-widest text-lg font-bold"
                  />
                  <Button
                    :disabled="twoFactor.isConfirming.value || !confirmCode"
                    @click="handleConfirm2FA"
                  >
                    <Loader2 v-if="twoFactor.isConfirming.value" class="h-4 w-4 animate-spin mr-1" />
                    Verify
                  </Button>
                  <Button
                    variant="outline"
                    type="button"
                    :disabled="twoFactor.isConfirming.value || twoFactor.isDisabling.value"
                    @click="handleCancelSetup"
                  >
                    Cancel
                  </Button>
                </div>
              </div>
            </div>

            <!-- 2FA is currently Enabled -->
            <div v-if="is2FAEnabled" class="space-y-6">
              <div class="flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                <CheckCircle2 class="h-4 w-4" />
                Two-factor authentication is active on your account.
              </div>

              <!-- Recovery Codes Section -->
              <div class="space-y-3 p-4 rounded-lg bg-muted/40 border border-border/50">
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-xs font-semibold text-foreground">Emergency Recovery Codes</h4>
                    <p class="text-[11px] text-muted-foreground">
                      Store these backup codes in a safe place. You can use them if you lose access to your device.
                    </p>
                  </div>
                  <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" class="h-7 text-xs gap-1.5" @click="twoFactor.fetchRecoveryCodes">
                      View Codes
                    </Button>
                    <Button
                      v-if="twoFactor.recoveryCodes.value.length"
                      variant="outline"
                      size="sm"
                      class="h-7 text-xs gap-1.5"
                      @click="copyRecoveryCodes"
                    >
                      <Copy class="h-3 w-3" />
                      Copy
                    </Button>
                    <Button
                      variant="outline"
                      size="sm"
                      class="h-7 text-xs gap-1.5"
                      :disabled="twoFactor.isRegenerating.value"
                      @click="twoFactor.regenerateRecoveryCodes"
                    >
                      <RefreshCw class="h-3 w-3" :class="{ 'animate-spin': twoFactor.isRegenerating.value }" />
                      Regenerate
                    </Button>
                  </div>
                </div>

                <div
                  v-if="twoFactor.recoveryCodes.value.length"
                  class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2"
                >
                  <div
                    v-for="code in twoFactor.recoveryCodes.value"
                    :key="code"
                    class="font-mono text-xs p-2 rounded bg-background border border-border/60 text-center font-medium select-all"
                  >
                    {{ code }}
                  </div>
                </div>
              </div>

              <!-- Disable 2FA button -->
              <Button
                variant="destructive"
                :disabled="twoFactor.isDisabling.value"
                class="gap-2"
                @click="handleDisable2FA"
              >
                <Loader2 v-if="twoFactor.isDisabling.value" class="h-4 w-4 animate-spin" />
                <ShieldAlert v-else class="h-4 w-4" />
                Disable Two-Factor Authentication
              </Button>
            </div>
          </CardContent>
        </Card>
      </TabsContent>
    </Tabs>
    </div>
  </div>
</template>
