<script setup lang="ts">
import { computed } from 'vue'
import { Languages, Check } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

const { locale, locales, setLocale, t } = useI18n()

interface LocaleItem {
  code: string
  name: string
  dir?: string
  flag?: string
}

const localeList = computed<LocaleItem[]>(() => {
  return [
    { code: 'en', name: 'English', dir: 'ltr', flag: '🇺🇸' },
    { code: 'fr', name: 'Français', dir: 'ltr', flag: '🇫🇷' },
    { code: 'ar', name: 'العربية', dir: 'rtl', flag: '🇸🇦' },
  ]
})

const currentLocale = computed(() => {
  return localeList.value.find(l => l.code === locale.value) || localeList.value[0]
})

function changeLocale(targetCode: string) {
  setLocale(targetCode as 'en' | 'fr' | 'ar')
}
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button
        variant="ghost"
        size="sm"
        class="h-9 px-2.5 flex items-center gap-1.5 rounded-md hover:bg-accent text-foreground transition-colors"
        :title="t('common.switch_language')"
      >
        <Languages class="h-4 w-4 text-muted-foreground" />
        <span class="text-xs font-semibold uppercase tracking-wider">
          {{ currentLocale?.code }}
        </span>
        <span class="sr-only">{{ t('common.switch_language') }}</span>
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end" class="w-44">
      <DropdownMenuLabel class="text-xs font-medium text-muted-foreground">
        {{ t('common.language') }}
      </DropdownMenuLabel>
      <DropdownMenuSeparator />
      <DropdownMenuItem
        v-for="item in localeList"
        :key="item.code"
        class="flex items-center justify-between cursor-pointer py-2"
        :class="{ 'font-semibold bg-accent/50': item.code === locale }"
        @click="changeLocale(item.code)"
      >
        <div class="flex items-center gap-2">
          <span class="text-sm">{{ item.flag }}</span>
          <span class="text-sm">{{ item.name }}</span>
        </div>
        <Check
          v-if="item.code === locale"
          class="h-4 w-4 text-primary shrink-0"
        />
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
