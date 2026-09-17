<script setup lang="ts">
import { IconTrendingDown, IconTrendingUp } from "@tabler/icons-vue"

import { Badge } from '@/components/ui/badge'
import {
  Card,
  CardAction,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
</script>

<template>
  <div class="*:data-[slot=card]:from-primary/5 *:data-[slot=card]:to-card dark:*:data-[slot=card]:bg-card grid grid-cols-1 gap-4 px-4 *:data-[slot=card]:bg-gradient-to-t *:data-[slot=card]:shadow-xs lg:px-6 @xl/main:grid-cols-2 @5xl/main:grid-cols-4">
    <Card class="@container/card">
      <CardHeader>
        <CardDescription>{{ $t('dashboard.total_revenue') }}</CardDescription>
        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
          $1,250.00
        </CardTitle>
        <CardAction>
          <Badge variant="outline">
            <IconTrendingUp />
            +12.5%
          </Badge>
        </CardAction>
      </CardHeader>
      <CardFooter class="flex-col items-start gap-1.5 text-sm">
        <div class="line-clamp-1 flex gap-2 font-medium">
          {{ $t('dashboard.trending_up_month') }} <IconTrendingUp class="size-4" />
        </div>
        <div class="text-muted-foreground">
          {{ $t('dashboard.visitors_last_6_months') }}
        </div>
      </CardFooter>
    </Card>
    <Card class="@container/card">
      <CardHeader>
        <CardDescription>{{ $t('dashboard.new_customers') }}</CardDescription>
        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
          1,234
        </CardTitle>
        <CardAction>
          <Badge variant="outline">
            <IconTrendingDown />
            -20%
          </Badge>
        </CardAction>
      </CardHeader>
      <CardFooter class="flex-col items-start gap-1.5 text-sm">
        <div class="line-clamp-1 flex gap-2 font-medium">
          {{ $t('dashboard.down_period') }} <IconTrendingDown class="size-4" />
        </div>
        <div class="text-muted-foreground">
          {{ $t('dashboard.acquisition_needs_attention') }}
        </div>
      </CardFooter>
    </Card>
    <Card class="@container/card">
      <CardHeader>
        <CardDescription>{{ $t('dashboard.active_accounts') }}</CardDescription>
        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
          45,678
        </CardTitle>
        <CardAction>
          <Badge variant="outline">
            <IconTrendingUp />
            +12.5%
          </Badge>
        </CardAction>
      </CardHeader>
      <CardFooter class="flex-col items-start gap-1.5 text-sm">
        <div class="line-clamp-1 flex gap-2 font-medium">
          {{ $t('dashboard.strong_user_retention') }} <IconTrendingUp class="size-4" />
        </div>
        <div class="text-muted-foreground">
          {{ $t('dashboard.engagement_exceed_targets') }}
        </div>
      </CardFooter>
    </Card>
    <Card class="@container/card">
      <CardHeader>
        <CardDescription>{{ $t('dashboard.growth_rate') }}</CardDescription>
        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
          4.5%
        </CardTitle>
        <CardAction>
          <Badge variant="outline">
            <IconTrendingUp />
            +4.5%
          </Badge>
        </CardAction>
      </CardHeader>
      <CardFooter class="flex-col items-start gap-1.5 text-sm">
        <div class="line-clamp-1 flex gap-2 font-medium">
          {{ $t('dashboard.steady_performance_increase') }} <IconTrendingUp class="size-4" />
        </div>
        <div class="text-muted-foreground">
          {{ $t('dashboard.meets_growth_projections') }}
        </div>
      </CardFooter>
    </Card>
  </div>
</template>
