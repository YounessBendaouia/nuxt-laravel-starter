<script setup lang="ts">
import { ref, computed } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Progress } from '@/components/ui/progress'
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  Sparkles,
  ShieldAlert,
  CheckCircle2,
  AlertTriangle,
  FileText,
  Search,
  Filter,
  MoreVertical,
  Cpu,
  Layers,
  ArrowUpRight,
  Download,
  CheckCheck,
  Eye,
  RefreshCw,
  PlusCircle,
  Tag,
  Shield,
  FileCheck,
} from '@lucide/vue'
import { toast } from 'vue-sonner'

definePageMeta({
  layout: 'dashboard',
  middleware: ['sanctum:auth'],
  title: 'Classification',
})

const { t, te } = useI18n()

export type SensitivityLevel = 'public' | 'internal' | 'confidential' | 'restricted'
export type ClassificationStatus = 'verified' | 'auto_tagged' | 'needs_review'

export interface ClassifiedItem {
  id: string
  title: string
  contentSnippet: string
  category: string
  confidence: number
  sensitivity: SensitivityLevel
  entities: string[]
  status: ClassificationStatus
  model: string
  date: string
}

// Sandbox presets
const presets = [
  {
    label: 'Billing Dispute',
    model: 'DeBERTa-v3-Enterprise-ZeroShot',
    text: 'Customer requested immediate refund of $480.00 for double billing on account AC-98421. Credit card ending in 4242 was charged twice.',
    category: 'Financial & Billing',
    confidence: 98,
    sensitivity: 'confidential' as SensitivityLevel,
    entities: ['Credit Card: 4242', 'Amount: $480.00', 'Account: AC-98421'],
    action: 'Auto-route to Finance & Mask Account Number',
    breakdown: [
      { name: 'Financial & Billing', score: 98 },
      { name: 'Customer Support', score: 1.5 },
      { name: 'General Inquiry', score: 0.5 },
    ],
  },
  {
    label: 'API Key Leak',
    model: 'RoBERTa-Compliance-Guard-v2',
    text: 'CRITICAL ALERT: Environment variable dump contains live secret sk_live_51Mza489248xkls2948123 along with database connection URI.',
    category: 'Security Incident',
    confidence: 99,
    sensitivity: 'restricted' as SensitivityLevel,
    entities: ['Stripe Secret Key', 'DB Connection URI', 'Credentials Leak'],
    action: 'Trigger Automated Credential Revocation & Alert SecOps',
    breakdown: [
      { name: 'Security Incident', score: 99 },
      { name: 'DevOps / Infra', score: 0.8 },
      { name: 'Documentation', score: 0.2 },
    ],
  },
  {
    label: 'GDPR Removal',
    model: 'RoBERTa-Compliance-Guard-v2',
    text: 'Under Article 17 of the GDPR (Right to Erasure), I hereby request the immediate deletion of all personal data, IP logs, and purchase records associated with user@domain.eu.',
    category: 'Legal & Compliance',
    confidence: 96,
    sensitivity: 'confidential' as SensitivityLevel,
    entities: ['GDPR Art. 17', 'Email: user@domain.eu', 'PII Erasure'],
    action: 'Initiate 30-Day Compliance Deletion Workflow',
    breakdown: [
      { name: 'Legal & Compliance', score: 96 },
      { name: 'Privacy Inquiries', score: 3 },
      { name: 'General', score: 1 },
    ],
  },
  {
    label: 'Feature Feedback',
    model: 'LLaMA-3-Intent-Sentiment-Classifier',
    text: 'We really love the new dark mode aesthetics and the quick mail preview! Would be wonderful to have custom tags exportable to CSV.',
    category: 'Product Feedback',
    confidence: 92,
    sensitivity: 'public' as SensitivityLevel,
    entities: ['Sentiment: Positive', 'Feature: Dark Mode', 'Feature: CSV Export'],
    action: 'Aggregate into Monthly UX Prioritization Sprint',
    breakdown: [
      { name: 'Product Feedback', score: 92 },
      { name: 'Customer Praise', score: 6 },
      { name: 'Support', score: 2 },
    ],
  },
]

// Initial catalog records
const initialCatalog: ClassifiedItem[] = [
  {
    id: 'CLS-401',
    title: 'Customer Invoice #9842 & Payment Receipt',
    contentSnippet: 'Payment receipt confirmation for wire transfer of $14,200.00 to Acme Corp.',
    category: 'Financial',
    confidence: 98,
    sensitivity: 'confidential',
    entities: ['Wire Transfer', 'IBAN Masked', 'Invoice #9842'],
    status: 'verified',
    model: 'DeBERTa-v3-ZeroShot',
    date: 'Oct 14, 2026',
  },
  {
    id: 'CLS-402',
    title: 'OAuth2 Sanctum Authentication Log',
    contentSnippet: 'Bearer token exchange log with client credentials and IP 192.168.1.45',
    category: 'Security',
    confidence: 95,
    sensitivity: 'restricted',
    entities: ['IPv4 Address', 'Auth Token', 'Session Hash'],
    status: 'auto_tagged',
    model: 'RoBERTa-Security-Guard',
    date: 'Oct 14, 2026',
  },
  {
    id: 'CLS-403',
    title: 'Employee Performance Review & Compensation Q3',
    contentSnippet: 'Annual salary adjustments, performance grading, and stock option bonus calculations.',
    category: 'Human Resources',
    confidence: 91,
    sensitivity: 'confidential',
    entities: ['Compensation Data', 'Full Names', 'Performance Band'],
    status: 'needs_review',
    model: 'DeBERTa-v3-ZeroShot',
    date: 'Oct 13, 2026',
  },
  {
    id: 'CLS-404',
    title: 'Open Source API Documentation Guide',
    contentSnippet: 'Public reference documentation for deploying Laravel Sanctum with Nuxt 4.',
    category: 'Engineering',
    confidence: 99,
    sensitivity: 'public',
    entities: ['Code Snippets', 'Tutorial', 'Nuxt 4'],
    status: 'verified',
    model: 'LLaMA-3-Classifier',
    date: 'Oct 12, 2026',
  },
  {
    id: 'CLS-405',
    title: 'Enterprise Master Services Agreement (MSA)',
    contentSnippet: 'Draft mutual non-disclosure and service level agreement terms with 99.9% uptime SLA.',
    category: 'Legal',
    confidence: 88,
    sensitivity: 'confidential',
    entities: ['NDA Clauses', 'SLA 99.9%', 'Liability Terms'],
    status: 'needs_review',
    model: 'RoBERTa-Compliance-Guard',
    date: 'Oct 11, 2026',
  },
  {
    id: 'CLS-406',
    title: 'Customer Support Escalation Ticket #219',
    contentSnippet: 'User reported inability to access mail panel after migrating database to postgres.',
    category: 'Customer Support',
    confidence: 96,
    sensitivity: 'internal',
    entities: ['Bug Report', 'PostgreSQL', 'High Priority'],
    status: 'verified',
    model: 'LLaMA-3-Classifier',
    date: 'Oct 10, 2026',
  },
]

const catalog = ref<ClassifiedItem[]>(initialCatalog)
const selectedCategory = ref('all')
const selectedSensitivity = ref('all')
const activeCatalogTab = ref<'all' | 'pending' | 'sensitive' | 'verified'>('all')
const searchQuery = ref('')
const selectedItemIds = ref<string[]>([])

// Sandbox state
const sandboxText = ref(presets[0].text)
const sandboxModel = ref('DeBERTa-v3-Enterprise-ZeroShot')
const isAnalyzing = ref(false)
const sandboxResult = ref<any>({
  category: presets[0].category,
  confidence: presets[0].confidence,
  sensitivity: presets[0].sensitivity,
  entities: presets[0].entities,
  action: presets[0].action,
  breakdown: presets[0].breakdown,
})

// Item detail modal
const activeItem = ref<ClassifiedItem | null>(null)
const isDetailOpen = ref(false)

// Metrics
const stats = computed(() => {
  const total = catalog.value.length
  const avgConfidence = Math.round(
    catalog.value.reduce((acc, curr) => acc + curr.confidence, 0) / (total || 1)
  )
  const needsReview = catalog.value.filter((i) => i.status === 'needs_review').length
  const sensitiveCount = catalog.value.filter(
    (i) => i.sensitivity === 'confidential' || i.sensitivity === 'restricted'
  ).length
  return { total, avgConfidence, needsReview, sensitiveCount }
})

// Filtered Catalog
const filteredCatalog = computed(() => {
  return catalog.value.filter((item) => {
    // Tab filtering
    if (activeCatalogTab.value === 'pending' && item.status !== 'needs_review') return false
    if (activeCatalogTab.value === 'sensitive' && item.sensitivity !== 'confidential' && item.sensitivity !== 'restricted') return false
    if (activeCatalogTab.value === 'verified' && item.status !== 'verified') return false

    // Category filter
    if (selectedCategory.value !== 'all' && item.category !== selectedCategory.value) return false

    // Sensitivity filter
    if (selectedSensitivity.value !== 'all' && item.sensitivity !== selectedSensitivity.value) return false

    // Search filter
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase()
      return (
        item.title.toLowerCase().includes(q) ||
        item.contentSnippet.toLowerCase().includes(q) ||
        item.id.toLowerCase().includes(q) ||
        item.category.toLowerCase().includes(q)
      )
    }

    return true
  })
})

function applyPreset(preset: typeof presets[0]) {
  sandboxText.value = preset.text
  sandboxModel.value = preset.model
  sandboxResult.value = {
    category: preset.category,
    confidence: preset.confidence,
    sensitivity: preset.sensitivity,
    entities: preset.entities,
    action: preset.action,
    breakdown: preset.breakdown,
  }
}

function runAnalysis() {
  if (!sandboxText.value.trim()) {
    toast.error('Please input content to classify')
    return
  }

  isAnalyzing.value = true
  setTimeout(() => {
    isAnalyzing.value = false
    // Generate realistic result based on text keywords
    const textLower = sandboxText.value.toLowerCase()
    let predicted = 'General Document'
    let conf = 94
    let sens: SensitivityLevel = 'internal'
    let ents = ['Context Extracted', 'NLP Tokens']
    let act = 'Classify and index in document catalog'

    if (textLower.includes('refund') || textLower.includes('billing') || textLower.includes('card') || textLower.includes('$')) {
      predicted = 'Financial & Billing'
      conf = 98
      sens = 'confidential'
      ents = ['Monetary Token', 'Account Masking Required', 'Financial Intent']
      act = 'Route to Accounting & Mask sensitive payment details'
    } else if (textLower.includes('secret') || textLower.includes('key') || textLower.includes('token') || textLower.includes('leak')) {
      predicted = 'Security & Vulnerability'
      conf = 99
      sens = 'restricted'
      ents = ['Cryptographic Key', 'High Risk Indicator', 'SecOps Event']
      act = 'Immediate Quarantine & Alert SOC Engineering'
    } else if (textLower.includes('gdpr') || textLower.includes('delete') || textLower.includes('privacy') || textLower.includes('legal')) {
      predicted = 'Legal & Regulatory'
      conf = 96
      sens = 'confidential'
      ents = ['Regulatory Article', 'Subject Data Request', 'Compliance Watch']
      act = 'Log in Compliance Ledger & Notify Data Protection Officer'
    } else {
      predicted = 'Product & Feedback'
      conf = 92
      sens = 'public'
      ents = ['User Experience', 'Feature Mention', 'Sentiment Positive']
      act = 'Assign to Product Backlog Queue'
    }

    sandboxResult.value = {
      category: predicted,
      confidence: conf,
      sensitivity: sens,
      entities: ents,
      action: act,
      breakdown: [
        { name: predicted, score: conf },
        { name: 'Secondary Classification', score: Math.max(1, 100 - conf - 2) },
        { name: 'Residual Risk', score: 2 },
      ],
    }

    toast.success('Classification completed with high confidence!')
  }, 650)
}

function saveSandboxToCatalog() {
  const newItem: ClassifiedItem = {
    id: `CLS-${Math.floor(400 + Math.random() * 500)}`,
    title: sandboxText.value.slice(0, 48) + '...',
    contentSnippet: sandboxText.value,
    category: sandboxResult.value.category,
    confidence: sandboxResult.value.confidence,
    sensitivity: sandboxResult.value.sensitivity,
    entities: sandboxResult.value.entities,
    status: 'auto_tagged',
    model: sandboxModel.value,
    date: 'Just now',
  }

  catalog.value.unshift(newItem)
  toast.success(`Saved item ${newItem.id} into classification catalog!`)
}

function approveItem(item: ClassifiedItem) {
  item.status = 'verified'
  toast.success(`Item ${item.id} verified and approved`)
}

function approveSelected() {
  if (selectedItemIds.value.length === 0) {
    toast.error('No items selected')
    return
  }
  catalog.value.forEach((i) => {
    if (selectedItemIds.value.includes(i.id)) {
      i.status = 'verified'
    }
  })
  toast.success(`Approved ${selectedItemIds.value.length} items`)
  selectedItemIds.value = []
}

function exportCsv() {
  toast.success('Exporting classification inventory to CSV...')
}

function toggleSelectItem(id: string) {
  const idx = selectedItemIds.value.indexOf(id)
  if (idx > -1) {
    selectedItemIds.value.splice(idx, 1)
  } else {
    selectedItemIds.value.push(id)
  }
}

function openDetail(item: ClassifiedItem) {
  activeItem.value = item
  isDetailOpen.value = true
}

function getSensitivityBadge(sens: SensitivityLevel) {
  switch (sens) {
    case 'restricted':
      return { class: 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20', label: 'Restricted (Tier 1)' }
    case 'confidential':
      return { class: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20', label: 'Confidential' }
    case 'internal':
      return { class: 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20', label: 'Internal Only' }
    case 'public':
      return { class: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20', label: 'Public' }
  }
}

function getStatusBadge(status: ClassificationStatus) {
  switch (status) {
    case 'verified':
      return { class: 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20', label: 'Verified' }
    case 'auto_tagged':
      return { class: 'bg-blue-500/10 text-blue-600 border-blue-500/20', label: 'AI Auto-Tagged' }
    case 'needs_review':
      return { class: 'bg-amber-500/10 text-amber-600 border-amber-500/20', label: 'Needs Review' }
  }
}
</script>

<template>
  <div class="flex flex-col gap-6 p-4 md:p-6 lg:p-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
          {{ te('classification_page.title') ? t('classification_page.title') : 'Classification Studio' }}
        </h1>
        <p class="text-sm text-muted-foreground mt-1">
          {{ te('classification_page.description') ? t('classification_page.description') : 'AI-powered data classification, PII risk detection, and regulatory compliance tagging.' }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <Button variant="outline" class="gap-2 shadow-xs text-xs" @click="exportCsv">
          <Download class="size-3.5" />
          <span>{{ te('classification_page.catalog.export_csv') ? t('classification_page.catalog.export_csv') : 'Export CSV' }}</span>
        </Button>
      </div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
      <Card class="border-border/60 shadow-xs">
        <CardContent class="p-4 flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-muted-foreground">
              {{ te('classification_page.stats.total') ? t('classification_page.stats.total') : 'Total Classified' }}
            </p>
            <p class="text-2xl font-bold mt-1">{{ stats.total }}</p>
          </div>
          <div class="p-2.5 rounded-xl bg-primary/10 text-primary">
            <Layers class="size-5" />
          </div>
        </CardContent>
      </Card>

      <Card class="border-border/60 shadow-xs">
        <CardContent class="p-4 flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-muted-foreground">
              {{ te('classification_page.stats.accuracy') ? t('classification_page.stats.accuracy') : 'Confidence Average' }}
            </p>
            <div class="flex items-center gap-2 mt-1">
              <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.avgConfidence }}%</span>
              <span class="text-[11px] text-muted-foreground font-mono">High Quality</span>
            </div>
          </div>
          <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-500">
            <CheckCircle2 class="size-5" />
          </div>
        </CardContent>
      </Card>

      <Card class="border-border/60 shadow-xs">
        <CardContent class="p-4 flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-muted-foreground">
              {{ te('classification_page.stats.review') ? t('classification_page.stats.review') : 'Requires Review' }}
            </p>
            <p class="text-2xl font-bold mt-1 text-amber-600 dark:text-amber-400">{{ stats.needsReview }}</p>
          </div>
          <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-500">
            <AlertTriangle class="size-5" />
          </div>
        </CardContent>
      </Card>

      <Card class="border-border/60 shadow-xs">
        <CardContent class="p-4 flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-muted-foreground">
              {{ te('classification_page.stats.pii') ? t('classification_page.stats.pii') : 'Sensitive / PII Flagged' }}
            </p>
            <p class="text-2xl font-bold mt-1 text-red-600 dark:text-red-400">{{ stats.sensitiveCount }}</p>
          </div>
          <div class="p-2.5 rounded-xl bg-red-500/10 text-red-500">
            <ShieldAlert class="size-5" />
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Interactive Live AI Classification Sandbox -->
    <Card class="border-border/70 shadow-sm overflow-hidden">
      <CardHeader class="pb-3 border-b border-border/50 bg-muted/20">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="flex items-center gap-2.5">
            <div class="p-2 rounded-lg bg-primary/10 text-primary">
              <Sparkles class="size-4" />
            </div>
            <div>
              <CardTitle class="text-base font-semibold">
                {{ te('classification_page.sandbox.title') ? t('classification_page.sandbox.title') : 'Live AI Classifier Sandbox' }}
              </CardTitle>
              <CardDescription class="text-xs">
                {{ te('classification_page.sandbox.description') ? t('classification_page.sandbox.description') : 'Test classification models against raw text, customer messages, or document snippets.' }}
              </CardDescription>
            </div>
          </div>

          <!-- Quick presets pills -->
          <div class="flex items-center gap-1.5 flex-wrap">
            <span class="text-[11px] text-muted-foreground font-medium me-1">Presets:</span>
            <Button
              v-for="p in presets"
              :key="p.label"
              variant="outline"
              size="sm"
              class="h-7 text-[11px] px-2 py-0 border-border/60 hover:bg-primary/10 hover:text-primary transition-colors"
              @click="applyPreset(p)"
            >
              {{ p.label }}
            </Button>
          </div>
        </div>
      </CardHeader>

      <CardContent class="p-4 md:p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: Input Area -->
        <div class="lg:col-span-7 flex flex-col gap-3.5">
          <div class="flex items-center justify-between">
            <Label class="text-xs font-semibold text-muted-foreground">
              {{ te('classification_page.sandbox.input_label') ? t('classification_page.sandbox.input_label') : 'Input Content' }}
            </Label>

            <!-- Model Selector -->
            <div class="flex items-center gap-2">
              <span class="text-[11px] text-muted-foreground">Model:</span>
              <Select v-model="sandboxModel">
                <SelectTrigger class="h-8 text-xs w-52 bg-background">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="DeBERTa-v3-Enterprise-ZeroShot">DeBERTa-v3-ZeroShot (Accurate)</SelectItem>
                  <SelectItem value="RoBERTa-Compliance-Guard-v2">RoBERTa-Compliance-Guard</SelectItem>
                  <SelectItem value="LLaMA-3-Intent-Sentiment-Classifier">LLaMA-3-Intent-Classifier</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <Textarea
            v-model="sandboxText"
            :placeholder="te('classification_page.sandbox.input_placeholder') ? t('classification_page.sandbox.input_placeholder') : 'Paste raw text to classify...'"
            rows="5"
            class="text-xs leading-relaxed font-mono bg-background resize-none"
          />

          <div class="flex items-center justify-between pt-1">
            <span class="text-[11px] text-muted-foreground font-mono">
              {{ sandboxText.length }} characters
            </span>

            <Button
              :disabled="isAnalyzing"
              class="gap-2 shadow-xs"
              @click="runAnalysis"
            >
              <Cpu class="size-4" :class="{ 'animate-spin': isAnalyzing }" />
              <span>
                {{ isAnalyzing ? 'Analyzing tokens...' : 'Run AI Classification' }}
              </span>
            </Button>
          </div>
        </div>

        <!-- Right: Real-time Output Card -->
        <div class="lg:col-span-5 flex flex-col rounded-xl border border-border/70 bg-muted/20 p-4 gap-4">
          <div class="flex items-center justify-between pb-2 border-b border-border/40">
            <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
              <Shield class="size-3.5 text-primary" />
              <span>Predicted Inference</span>
            </h3>

            <Button
              size="sm"
              variant="outline"
              class="h-7 text-[11px] gap-1 shadow-2xs"
              @click="saveSandboxToCatalog"
            >
              <PlusCircle class="size-3" />
              <span>Save to Catalog</span>
            </Button>
          </div>

          <!-- Main Class & Confidence Meter -->
          <div class="flex flex-col gap-1.5 bg-card p-3 rounded-lg border border-border/50">
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold text-muted-foreground">Predicted Class</span>
              <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400">
                {{ sandboxResult.confidence }}% Confidence
              </span>
            </div>
            <p class="text-base font-bold text-foreground">
              {{ sandboxResult.category }}
            </p>
            <Progress :model-value="sandboxResult.confidence" class="h-1.5 mt-1 bg-muted" />
          </div>

          <!-- Sensitivity Tier & Guardrails -->
          <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="bg-card p-2.5 rounded-lg border border-border/50 flex flex-col gap-1">
              <span class="text-[10px] text-muted-foreground font-medium">Sensitivity Tier</span>
              <Badge
                variant="outline"
                class="w-fit text-[11px] capitalize"
                :class="getSensitivityBadge(sandboxResult.sensitivity).class"
              >
                {{ getSensitivityBadge(sandboxResult.sensitivity).label }}
              </Badge>
            </div>

            <div class="bg-card p-2.5 rounded-lg border border-border/50 flex flex-col gap-1">
              <span class="text-[10px] text-muted-foreground font-medium">Entities Flagged</span>
              <span class="font-bold text-xs">{{ sandboxResult.entities.length }} Detected</span>
            </div>
          </div>

          <!-- Detected Entities Chips -->
          <div class="flex flex-col gap-1.5">
            <span class="text-[11px] font-semibold text-muted-foreground">Detected Guardrails:</span>
            <div class="flex flex-wrap gap-1">
              <Badge
                v-for="e in sandboxResult.entities"
                :key="e"
                variant="secondary"
                class="text-[10px] font-mono px-2 py-0.5"
              >
                {{ e }}
              </Badge>
            </div>
          </div>

          <!-- Recommended Action -->
          <div class="bg-primary/5 border border-primary/20 p-2.5 rounded-lg text-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-primary">Automated Policy:</span>
            <p class="text-foreground/90 font-medium mt-0.5">{{ sandboxResult.action }}</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Classification Inventory Data Table -->
    <Card class="border-border/70 shadow-sm overflow-hidden">
      <CardHeader class="pb-3 border-b border-border/50">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <CardTitle class="text-base font-semibold">
              {{ te('classification_page.catalog.title') ? t('classification_page.catalog.title') : 'Classified Asset Inventory' }}
            </CardTitle>
            <CardDescription class="text-xs">
              {{ te('classification_page.catalog.description') ? t('classification_page.catalog.description') : 'Catalog of classified tickets, files, and database entities.' }}
            </CardDescription>
          </div>

          <div class="flex items-center gap-2">
            <Button
              v-if="selectedItemIds.length > 0"
              size="sm"
              class="h-8 text-xs gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white"
              @click="approveSelected"
            >
              <CheckCheck class="size-3.5" />
              <span>Approve ({{ selectedItemIds.length }})</span>
            </Button>
          </div>
        </div>

        <!-- Filter Controls Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pt-3">
          <Tabs v-model="activeCatalogTab" class="w-full md:w-auto">
            <TabsList class="h-8">
              <TabsTrigger value="all" class="text-xs">All ({{ catalog.length }})</TabsTrigger>
              <TabsTrigger value="pending" class="text-xs">Needs Review ({{ stats.needsReview }})</TabsTrigger>
              <TabsTrigger value="sensitive" class="text-xs">Confidential / PII ({{ stats.sensitiveCount }})</TabsTrigger>
              <TabsTrigger value="verified" class="text-xs">Verified</TabsTrigger>
            </TabsList>
          </Tabs>

          <div class="flex items-center gap-2">
            <div class="relative w-48 sm:w-64">
              <Search class="absolute start-2.5 top-1/2 -translate-y-1/2 size-3.5 text-muted-foreground" />
              <Input
                v-model="searchQuery"
                placeholder="Filter by title, ID, tag..."
                class="ps-8 h-8 text-xs bg-background"
              />
            </div>

            <Select v-model="selectedCategory">
              <SelectTrigger class="h-8 w-32 text-xs bg-background">
                <SelectValue placeholder="Category" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Categories</SelectItem>
                <SelectItem value="Financial">Financial</SelectItem>
                <SelectItem value="Security">Security</SelectItem>
                <SelectItem value="Human Resources">Human Resources</SelectItem>
                <SelectItem value="Engineering">Engineering</SelectItem>
                <SelectItem value="Legal">Legal</SelectItem>
                <SelectItem value="Customer Support">Customer Support</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </CardHeader>

      <CardContent class="p-0 overflow-x-auto">
        <Table>
          <TableHeader class="bg-muted/30">
            <TableRow>
              <TableHead class="w-10 text-center">
                <span class="sr-only">Select</span>
              </TableHead>
              <TableHead class="min-w-[200px]">Asset / Content</TableHead>
              <TableHead>Category</TableHead>
              <TableHead>Confidence</TableHead>
              <TableHead>Sensitivity</TableHead>
              <TableHead>Entities</TableHead>
              <TableHead>Status</TableHead>
              <TableHead class="text-end">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="item in filteredCatalog"
              :key="item.id"
              class="hover:bg-muted/30 cursor-pointer transition-colors"
              @click="openDetail(item)"
            >
              <!-- Checkbox selection -->
              <TableCell class="text-center" @click.stop>
                <input
                  type="checkbox"
                  class="rounded border-border size-3.5 accent-primary cursor-pointer"
                  :checked="selectedItemIds.includes(item.id)"
                  @change="toggleSelectItem(item.id)"
                />
              </TableCell>

              <!-- Asset Title & Snippet -->
              <TableCell>
                <div class="flex flex-col">
                  <div class="flex items-center gap-1.5">
                    <span class="font-mono text-[11px] font-bold text-muted-foreground">{{ item.id }}</span>
                    <span class="font-semibold text-xs text-foreground">{{ item.title }}</span>
                  </div>
                  <p class="text-[11px] text-muted-foreground line-clamp-1 mt-0.5 font-normal">
                    {{ item.contentSnippet }}
                  </p>
                </div>
              </TableCell>

              <!-- Category -->
              <TableCell>
                <Badge variant="outline" class="text-xs font-medium">
                  {{ item.category }}
                </Badge>
              </TableCell>

              <!-- Confidence -->
              <TableCell>
                <div class="flex items-center gap-2">
                  <span class="text-xs font-mono font-semibold">{{ item.confidence }}%</span>
                  <Progress :model-value="item.confidence" class="w-14 h-1.5" />
                </div>
              </TableCell>

              <!-- Sensitivity -->
              <TableCell>
                <Badge
                  variant="outline"
                  class="text-[10px] capitalize"
                  :class="getSensitivityBadge(item.sensitivity).class"
                >
                  {{ getSensitivityBadge(item.sensitivity).label }}
                </Badge>
              </TableCell>

              <!-- Entities -->
              <TableCell>
                <div class="flex items-center gap-1">
                  <Badge
                    v-for="e in item.entities.slice(0, 2)"
                    :key="e"
                    variant="secondary"
                    class="text-[9px] font-mono px-1.5 py-0"
                  >
                    {{ e }}
                  </Badge>
                  <span v-if="item.entities.length > 2" class="text-[10px] text-muted-foreground">
                    +{{ item.entities.length - 2 }}
                  </span>
                </div>
              </TableCell>

              <!-- Status -->
              <TableCell>
                <Badge
                  variant="outline"
                  class="text-[10px]"
                  :class="getStatusBadge(item.status).class"
                >
                  {{ getStatusBadge(item.status).label }}
                </Badge>
              </TableCell>

              <!-- Actions -->
              <TableCell class="text-end" @click.stop>
                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button variant="ghost" size="icon" class="size-7 text-muted-foreground">
                      <MoreVertical class="size-3.5" />
                      <span class="sr-only">Actions</span>
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-40">
                    <DropdownMenuItem class="text-xs cursor-pointer gap-2" @click="openDetail(item)">
                      <Eye class="size-3.5" />
                      <span>View Details</span>
                    </DropdownMenuItem>
                    <DropdownMenuItem
                      v-if="item.status !== 'verified'"
                      class="text-xs text-emerald-600 cursor-pointer gap-2"
                      @click="approveItem(item)"
                    >
                      <CheckCheck class="size-3.5" />
                      <span>Approve Tag</span>
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </TableCell>
            </TableRow>

            <TableRow v-if="filteredCatalog.length === 0">
              <TableCell colspan="8" class="text-center py-10 text-muted-foreground text-xs">
                No matching classified assets found.
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>

    <!-- Item Details Modal -->
    <Dialog v-model:open="isDetailOpen">
      <DialogContent v-if="activeItem" class="sm:max-w-lg">
        <DialogHeader>
          <div class="flex items-center justify-between gap-2 me-4">
            <span class="font-mono text-xs font-bold text-muted-foreground">{{ activeItem.id }}</span>
            <Badge
              variant="outline"
              class="text-xs"
              :class="getSensitivityBadge(activeItem.sensitivity).class"
            >
              {{ getSensitivityBadge(activeItem.sensitivity).label }}
            </Badge>
          </div>
          <DialogTitle class="text-base mt-1">{{ activeItem.title }}</DialogTitle>
          <DialogDescription class="text-xs">
            Classified by {{ activeItem.model }} on {{ activeItem.date }}
          </DialogDescription>
        </DialogHeader>

        <div class="flex flex-col gap-3.5 py-2 text-xs">
          <div>
            <h4 class="font-semibold text-muted-foreground mb-1">Content Snippet</h4>
            <p class="bg-muted/30 p-3 rounded-lg border border-border/50 text-foreground font-mono leading-relaxed">
              {{ activeItem.contentSnippet }}
            </p>
          </div>

          <div class="grid grid-cols-2 gap-3 bg-muted/20 p-3 rounded-lg border border-border/40">
            <div>
              <span class="text-muted-foreground font-medium">Category:</span>
              <p class="font-bold text-sm mt-0.5">{{ activeItem.category }}</p>
            </div>
            <div>
              <span class="text-muted-foreground font-medium">Confidence:</span>
              <p class="font-bold text-sm text-emerald-600 mt-0.5">{{ activeItem.confidence }}%</p>
            </div>
          </div>

          <div>
            <h4 class="font-semibold text-muted-foreground mb-1">Detected Entities & PII Tokens</h4>
            <div class="flex flex-wrap gap-1.5">
              <Badge
                v-for="e in activeItem.entities"
                :key="e"
                variant="secondary"
                class="font-mono text-xs px-2 py-0.5"
              >
                {{ e }}
              </Badge>
            </div>
          </div>
        </div>

        <DialogFooter class="flex sm:justify-between items-center gap-2">
          <Button
            v-if="activeItem.status !== 'verified'"
            size="sm"
            class="text-xs gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white"
            @click="() => { approveItem(activeItem!); isDetailOpen = false }"
          >
            <CheckCheck class="size-3.5" />
            <span>Approve Classification</span>
          </Button>
          <div v-else></div>

          <Button size="sm" variant="outline" @click="isDetailOpen = false">
            {{ $t('common.close') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
