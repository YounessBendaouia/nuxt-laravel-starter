<script setup lang="ts">
import { ref, computed } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import {
  TrendingUp,
  Eye,
  MousePointerClick,
  Clock,
  Zap,
} from '@lucide/vue'
import {
  VisXYContainer,
  VisArea,
  VisLine,
  VisAxis,
  VisGroupedBar,
} from '@unovis/vue'
import { ChartContainer } from '@/components/ui/chart'

definePageMeta({
  layout: 'dashboard',
  middleware: ['sanctum:auth'],
  title: 'Analytics',
})

const { t } = useI18n()

const selectedPeriod = ref<'7d' | '30d' | '90d'>('30d')

interface MetricPoint {
  day: string
  views: number
  clicks: number
}

const analyticsData = computed<MetricPoint[]>(() => [
  { day: t('analytics_page.mon'), views: 2400, clicks: 840 },
  { day: t('analytics_page.tue'), views: 3100, clicks: 1020 },
  { day: t('analytics_page.wed'), views: 2800, clicks: 950 },
  { day: t('analytics_page.thu'), views: 4200, clicks: 1420 },
  { day: t('analytics_page.fri'), views: 3900, clicks: 1300 },
  { day: t('analytics_page.sat'), views: 1800, clicks: 610 },
  { day: t('analytics_page.sun'), views: 2100, clicks: 730 },
])

const chartConfig = computed(() => ({
  views: {
    label: t('analytics_page.chart_views'),
    color: 'var(--chart-1)',
  },
  clicks: {
    label: t('analytics_page.chart_engagements'),
    color: 'var(--chart-2)',
  },
}))
</script>

<template>
  <div class="flex flex-col gap-4 py-4 md:gap-6 md:py-6 px-4 lg:px-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight md:text-3xl">{{ t('analytics_page.title') }}</h1>
        <p class="text-sm text-muted-foreground">
          {{ t('analytics_page.description') }}
        </p>
      </div>

      <div class="flex items-center gap-1.5 bg-muted/60 p-1 rounded-lg border border-border/50">
        <Button
          size="sm"
          :variant="selectedPeriod === '7d' ? 'default' : 'ghost'"
          class="h-7 text-xs"
          @click="selectedPeriod = '7d'"
        >
          {{ t('analytics_page.days_7') }}
        </Button>
        <Button
          size="sm"
          :variant="selectedPeriod === '30d' ? 'default' : 'ghost'"
          class="h-7 text-xs"
          @click="selectedPeriod = '30d'"
        >
          {{ t('analytics_page.days_30') }}
        </Button>
        <Button
          size="sm"
          :variant="selectedPeriod === '90d' ? 'default' : 'ghost'"
          class="h-7 text-xs"
          @click="selectedPeriod = '90d'"
        >
          {{ t('analytics_page.days_90') }}
        </Button>
      </div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <Card class="border-border/60 shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
          <CardTitle class="text-sm font-medium text-muted-foreground">{{ t('analytics_page.total_views') }}</CardTitle>
          <Eye class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">128,450</div>
          <p class="text-xs text-emerald-500 font-medium mt-1 flex items-center gap-1">
            <TrendingUp class="h-3 w-3" />
            {{ t('analytics_page.views_trend') }}
          </p>
        </CardContent>
      </Card>

      <Card class="border-border/60 shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
          <CardTitle class="text-sm font-medium text-muted-foreground">{{ t('analytics_page.ctr') }}</CardTitle>
          <MousePointerClick class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">3.84%</div>
          <p class="text-xs text-emerald-500 font-medium mt-1 flex items-center gap-1">
            <TrendingUp class="h-3 w-3" />
            {{ t('analytics_page.ctr_trend') }}
          </p>
        </CardContent>
      </Card>

      <Card class="border-border/60 shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
          <CardTitle class="text-sm font-medium text-muted-foreground">{{ t('analytics_page.avg_session') }}</CardTitle>
          <Clock class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">4m 32s</div>
          <p class="text-xs text-muted-foreground mt-1">
            {{ t('analytics_page.session_trend') }}
          </p>
        </CardContent>
      </Card>

      <Card class="border-border/60 shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
          <CardTitle class="text-sm font-medium text-muted-foreground">{{ t('analytics_page.latency') }}</CardTitle>
          <Zap class="h-4 w-4 text-emerald-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">42ms</div>
          <p class="text-xs text-emerald-500 font-medium mt-1">
            {{ t('analytics_page.latency_sla') }}
          </p>
        </CardContent>
      </Card>
    </div>

    <!-- Views & Engagements Chart -->
    <Card class="border-border/60 shadow-sm">
      <CardHeader>
        <CardTitle class="text-lg font-semibold">{{ t('analytics_page.weekly_activity') }}</CardTitle>
        <CardDescription class="text-xs">{{ t('analytics_page.activity_description') }}</CardDescription>
      </CardHeader>
      <CardContent class="pt-2">
        <ClientOnly>
          <ChartContainer :config="chartConfig" class="h-[320px] w-full">
            <VisXYContainer :data="analyticsData">
              <VisArea
                :x="(d: MetricPoint, i: number) => i"
                :y="(d: MetricPoint) => d.views"
                color="var(--chart-1)"
                :opacity="0.2"
              />
              <VisLine
                :x="(d: MetricPoint, i: number) => i"
                :y="(d: MetricPoint) => d.views"
                color="var(--chart-1)"
                :lineWidth="2"
              />
              <VisGroupedBar
                :x="(d: MetricPoint, i: number) => i"
                :y="[(d: MetricPoint) => d.clicks]"
                color="var(--chart-2)"
                :roundedCorners="3"
                :barPadding="0.6"
              />
              <VisAxis type="x" :tickFormat="(i: number) => analyticsData[i]?.day || ''" :gridLine="false" />
              <VisAxis type="y" :gridLine="true" />
            </VisXYContainer>
          </ChartContainer>
          <template #fallback>
            <div class="h-[320px] w-full rounded-lg bg-muted/30 animate-pulse" />
          </template>
        </ClientOnly>

        <div class="flex items-center justify-center gap-6 mt-6 pt-3 border-t border-border/50 text-xs">
          <div class="flex items-center gap-2">
            <span class="h-2.5 w-2.5 rounded-full bg-[var(--chart-1)]" />
            <span class="text-muted-foreground">{{ t('analytics_page.legend_views') }}</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="h-2.5 w-2.5 rounded-full bg-[var(--chart-2)]" />
            <span class="text-muted-foreground">{{ t('analytics_page.legend_engagements') }}</span>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>
