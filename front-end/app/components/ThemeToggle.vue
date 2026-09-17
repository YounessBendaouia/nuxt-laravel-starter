<script setup lang="ts">
import { useDark, useToggle } from "@vueuse/core";
import { Sun, Moon } from "@lucide/vue";
import { Button } from "@/components/ui/button";

const { t } = useI18n();
const isDark = useDark();
const toggleDark = useToggle(isDark);
</script>

<template>
  <Button
    variant="ghost"
    size="icon"
    class="relative hover:bg-accent rounded-md w-9 h-9 transition-colors"
    :title="isDark ? t('common.switch_to_light') : t('common.switch_to_dark')"
    @click="toggleDark()"
  >
    <Sun
      class="w-[1.2rem] h-[1.2rem] text-amber-500 rotate-0 dark:-rotate-90 scale-100 dark:scale-0 transition-all"
    />
    <Moon
      class="absolute w-[1.2rem] h-[1.2rem] text-sky-400 rotate-90 dark:rotate-0 scale-0 dark:scale-100 transition-all"
    />
    <span class="sr-only">{{ t('common.toggle_theme') }}</span>
  </Button>
</template>
