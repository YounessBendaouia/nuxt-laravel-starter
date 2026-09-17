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

const { t } = useI18n()
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
    const msg = res?.message || t('banner.resend_sent')
    toast.success(t('banner.resend_sent'))
    resendVerificationText.value = t('settings_page.sent')
    setTimeout(() => {
      resendVerificationText.value = ''
    }, 4000)
  } catch (err: any) {
    const data = err?.data || err?.response?._data
    const msg = data?.message || t('banner.resend_failed')
    toast.error(t('banner.resend_failed'), { description: msg })
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
    const msg = res?.message || t('settings_page.changes_saved')
    profileSuccess.value = msg
    toast.success(t('settings_page.changes_saved'))
  } catch (err: any) {
    const data = err?.data || err?.response?._data
    profileError.value = data?.message || data?.errors?.email?.[0] || t('common.error_occurred')
    toast.error(profileError.value)
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
    const msg = res?.message || t('settings_page.password_updated')
    passwordSuccess.value = msg
    toast.success(t('settings_page.password_updated'))
    passwordForm.current_password = ''
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
  } catch (err: any) {
    const data = err?.data || err?.response?._data
    passwordError.value = data?.message || data?.errors?.current_password?.[0] || data?.errors?.password?.[0] || t('common.error_occurred')
    toast.error(passwordError.value)
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
  await twoFactor.enable()
}

async function handleConfirm2FA() {
  if (!confirmCode.value) return
  twoFactorSuccess.value = ''
  twoFactorError.value = ''
  const success = await twoFactor.confirm(confirmCode.value)
  if (success) {
    twoFactorSuccess.value = t('settings_page.two_factor_active')
    toast.success(t('settings_page.two_factor_active'))
    confirmCode.value = ''
    twoFactor.qrCode.value = ''
    await refreshIdentity()
  } else {
    twoFactorError.value = t('common.error_occurred')
    toast.error(twoFactorError.value)
  }
}

async function handleCancelSetup() {
  await twoFactor.disable()
  confirmCode.value = ''
  twoFactor.qrCode.value = ''
  twoFactorSuccess.value = ''
  twoFactorError.value = ''
  await refreshIdentity()
}

async function handleDisable2FA() {
  twoFactorSuccess.value = ''
  twoFactorError.value = ''
  const success = await twoFactor.disable()
  if (success) {
    toast.success(t('settings_page.disabled'))
    await refreshIdentity()
  } else {
    twoFactorError.value = t('common.error_occurred')
    toast.error(twoFactorError.value)
  }
}

async function handleConfirmPassword() {
  const success = await twoFactor.confirmPassword(confirmPasswordInput.value)
  if (success) {
    confirmPasswordInput.value = ''
  } else {
    toast.error(t('common.error_occurred'))
  }
}

function copyRecoveryCodes() {
  if (!twoFactor.recoveryCodes.value.length) return
  navigator.clipboard.writeText(twoFactor.recoveryCodes.value.join('\n'))
  toast.success(t('common.copied_to_clipboard'))
}
</script>

<template>
  <div class="flex flex-col gap-4 py-4 md:gap-6 md:py-6 px-4 lg:px-6">
    <div class="flex flex-col gap-1">
      <h1 class="text-2xl font-bold tracking-tight md:text-3xl">{{ t('settings_page.title') }}</h1>
      <p class="text-sm text-muted-foreground">
        {{ t('settings_page.description') }}
      </p>
    </div>

    <div class="max-w-4xl">
      <Tabs default-value="profile" class="space-y-6">
        <TabsList class="grid w-full grid-cols-3 max-w-md">
          <TabsTrigger value="profile" class="gap-2">
            <UserIcon class="h-4 w-4" />
            {{ t('settings_page.tab_profile') }}
          </TabsTrigger>
          <TabsTrigger value="password" class="gap-2">
            <Lock class="h-4 w-4" />
            {{ t('settings_page.tab_password') }}
          </TabsTrigger>
          <TabsTrigger value="security" class="gap-2">
            <ShieldCheck class="h-4 w-4" />
            {{ t('settings_page.tab_security') }}
          </TabsTrigger>
        </TabsList>

        <!-- TAB 1: Profile Information -->
        <TabsContent value="profile" class="space-y-6">
          <Card class="border-border/60 shadow-sm">
            <CardHeader>
              <CardTitle class="text-lg">{{ t('settings_page.profile_info') }}</CardTitle>
              <CardDescription class="text-xs">
                {{ t('settings_page.profile_description') }}
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
                  <Label for="name">{{ t('users_page.full_name') }}</Label>
                  <Input id="name" v-model="profileForm.name" required />
                </div>

                <div class="space-y-2">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <Label for="email">{{ t('users_page.email_address') }}</Label>
                      <Badge
                        :variant="isEmailVerified ? 'default' : 'outline'"
                        :class="isEmailVerified
                          ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/30 gap-1 text-[11px] font-medium'
                          : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30 gap-1 text-[11px] font-medium'"
                      >
                        <CheckCircle2 v-if="isEmailVerified" class="h-3 w-3" />
                        <AlertCircle v-else class="h-3 w-3" />
                        {{ isEmailVerified ? t('settings_page.verified') : t('settings_page.unverified') }}
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
                      <Loader2 v-if="isResendingVerification" class="h-3 w-3 animate-spin me-1" />
                      {{ resendVerificationText || t('settings_page.resend_link') }}
                    </Button>
                  </div>
                  <Input id="email" v-model="profileForm.email" type="email" required />
                </div>

                <div class="space-y-2">
                  <Label for="avatar">{{ t('settings_page.avatar_url') }}</Label>
                  <Input id="avatar" v-model="profileForm.avatar" placeholder="https://example.com/avatar.jpg" />
                </div>

                <Button type="submit" :disabled="isUpdatingProfile" class="font-medium">
                  <Loader2 v-if="isUpdatingProfile" class="me-2 h-4 w-4 animate-spin" />
                  <CheckCircle2 v-else-if="profileSuccess" class="me-2 h-4 w-4 text-emerald-500" />
                  {{ isUpdatingProfile ? t('settings_page.saving') : profileSuccess ? t('settings_page.changes_saved') : t('settings_page.save_profile_changes') }}
                </Button>
              </form>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- TAB 2: Update Password -->
        <TabsContent value="password" class="space-y-6">
          <Card class="border-border/60 shadow-sm">
            <CardHeader>
              <CardTitle class="text-lg">{{ t('settings_page.update_password') }}</CardTitle>
              <CardDescription class="text-xs">
                {{ t('settings_page.update_password_desc') }}
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
                  <Label for="currentPassword">{{ t('settings_page.current_password') }}</Label>
                  <Input id="currentPassword" v-model="passwordForm.current_password" type="password" required />
                </div>

                <div class="space-y-2">
                  <Label for="newPassword">{{ t('settings_page.new_password') }}</Label>
                  <Input id="newPassword" v-model="passwordForm.password" type="password" required />
                  <p class="text-[11px] text-muted-foreground">
                    {{ t('settings_page.password_hint') }}
                  </p>
                </div>

                <div class="space-y-2">
                  <Label for="confirmPassword">{{ t('settings_page.confirm_new_password') }}</Label>
                  <Input id="confirmPassword" v-model="passwordForm.password_confirmation" type="password" required />
                </div>

                <Button type="submit" :disabled="isUpdatingPassword" class="font-medium">
                  <Loader2 v-if="isUpdatingPassword" class="me-2 h-4 w-4 animate-spin" />
                  <CheckCircle2 v-else-if="passwordSuccess" class="me-2 h-4 w-4 text-emerald-500" />
                  {{ isUpdatingPassword ? t('settings_page.updating') : passwordSuccess ? t('settings_page.password_updated') : t('settings_page.update_password') }}
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
                <CardTitle class="text-base">{{ t('settings_page.confirm_pwd_title') }}</CardTitle>
              </div>
              <CardDescription class="text-xs">
                {{ t('settings_page.confirm_pwd_desc') }}
              </CardDescription>
            </CardHeader>
            <CardContent>
              <form class="space-y-4" @submit.prevent="handleConfirmPassword">
                <div class="space-y-2">
                  <Label for="secPassword">{{ t('settings_page.current_password') }}</Label>
                  <Input id="secPassword" v-model="confirmPasswordInput" type="password" required />
                </div>
                <Button type="submit">{{ t('settings_page.confirm_pwd_button') }}</Button>
              </form>
            </CardContent>
          </Card>

          <!-- Main 2FA Card -->
          <Card class="border-border/60 shadow-sm">
            <CardHeader class="flex flex-row items-center justify-between">
              <div class="space-y-1">
                <div class="flex items-center gap-2">
                  <CardTitle class="text-lg">{{ t('settings_page.two_factor_auth') }}</CardTitle>
                  <Badge
                    :variant="is2FAEnabled ? 'default' : 'secondary'"
                    :class="is2FAEnabled ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : ''"
                  >
                    {{ is2FAEnabled ? t('settings_page.enabled') : t('settings_page.disabled') }}
                  </Badge>
                </div>
                <CardDescription class="text-xs">
                  {{ t('settings_page.two_factor_desc') }}
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
                  {{ t('settings_page.two_factor_prompt') }}
                </p>
                <Button
                  :disabled="twoFactor.isEnabling.value"
                  class="font-medium gap-2"
                  @click="handleEnable2FA"
                >
                  <Loader2 v-if="twoFactor.isEnabling.value" class="h-4 w-4 animate-spin" />
                  <ShieldCheck v-else class="h-4 w-4" />
                  {{ t('settings_page.enable_2fa') }}
                </Button>
              </div>

              <!-- 2FA QR Code Setup in Progress -->
              <div v-if="!is2FAEnabled && twoFactor.qrCode.value" class="space-y-6 p-4 rounded-xl border border-primary/20 bg-primary/5 animate-in fade-in-50 duration-200">
                <div>
                  <h3 class="text-sm font-semibold text-foreground flex items-center gap-2">
                    <QrCode class="h-4 w-4 text-primary" />
                    {{ t('settings_page.step_1_title') }}
                  </h3>
                  <p class="text-xs text-muted-foreground mt-1">
                    {{ t('settings_page.step_1_desc') }}
                  </p>
                </div>

                <!-- QR Code SVG container -->
                <div class="p-4 bg-white rounded-lg inline-block shadow-sm" v-html="twoFactor.qrCode.value" />

                <div class="space-y-3 pt-2">
                  <h3 class="text-sm font-semibold text-foreground">
                    {{ t('settings_page.step_2_title') }}
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
                      <Loader2 v-if="twoFactor.isConfirming.value" class="h-4 w-4 animate-spin me-1" />
                      {{ t('settings_page.verify_button') }}
                    </Button>
                    <Button
                      variant="outline"
                      type="button"
                      :disabled="twoFactor.isConfirming.value || twoFactor.isDisabling.value"
                      @click="handleCancelSetup"
                    >
                      {{ t('common.cancel') }}
                    </Button>
                  </div>
                </div>
              </div>

              <!-- 2FA is currently Enabled -->
              <div v-if="is2FAEnabled" class="space-y-6">
                <div class="flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                  <CheckCircle2 class="h-4 w-4" />
                  {{ t('settings_page.two_factor_active') }}
                </div>

                <!-- Recovery Codes Section -->
                <div class="space-y-3 p-4 rounded-lg bg-muted/40 border border-border/50">
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-xs font-semibold text-foreground">{{ t('settings_page.recovery_codes_title') }}</h4>
                      <p class="text-[11px] text-muted-foreground">
                        {{ t('settings_page.recovery_codes_desc') }}
                      </p>
                    </div>
                    <div class="flex items-center gap-2">
                      <Button variant="outline" size="sm" class="h-7 text-xs gap-1.5" @click="twoFactor.fetchRecoveryCodes">
                        {{ t('settings_page.view_codes') }}
                      </Button>
                      <Button
                        v-if="twoFactor.recoveryCodes.value.length"
                        variant="outline"
                        size="sm"
                        class="h-7 text-xs gap-1.5"
                        @click="copyRecoveryCodes"
                      >
                        <Copy class="h-3 w-3" />
                        {{ t('common.copy') }}
                      </Button>
                      <Button
                        variant="outline"
                        size="sm"
                        class="h-7 text-xs gap-1.5"
                        :disabled="twoFactor.isRegenerating.value"
                        @click="twoFactor.regenerateRecoveryCodes"
                      >
                        <RefreshCw class="h-3 w-3" :class="{ 'animate-spin': twoFactor.isRegenerating.value }" />
                        {{ t('settings_page.regenerate') }}
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
                  {{ t('settings_page.disable_2fa') }}
                </Button>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  </div>
</template>
