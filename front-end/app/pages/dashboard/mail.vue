<script setup lang="ts">
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Separator } from '@/components/ui/separator'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { ScrollArea } from '@/components/ui/scroll-area'
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
  Inbox,
  Send,
  FileText,
  Archive,
  Trash2,
  AlertOctagon,
  Search,
  PenSquare,
  Star,
  Reply,
  ReplyAll,
  Forward,
  MoreVertical,
  Clock,
  Paperclip,
  Check,
  Tag,
  ArrowLeft,
  Download,
  MailCheck,
} from '@lucide/vue'
import { toast } from 'vue-sonner'

definePageMeta({
  layout: 'dashboard',
  middleware: ['sanctum:auth'],
  title: 'Mail',
})

const { t, te } = useI18n()

export type FolderId = 'inbox' | 'drafts' | 'sent' | 'junk' | 'trash' | 'archive'
export type MailTag = 'work' | 'personal' | 'urgent' | 'updates' | 'clients'

export interface MailAttachment {
  name: string
  size: string
  type: string
}

export interface MailMessage {
  id: string
  from: {
    name: string
    email: string
    initials: string
  }
  to: string
  subject: string
  snippet: string
  body: string
  date: string
  timestamp: string
  read: boolean
  starred: boolean
  folder: FolderId
  tags: MailTag[]
  attachments?: MailAttachment[]
}

const folders: { id: FolderId; labelKey: string; defaultLabel: string; icon: any }[] = [
  { id: 'inbox', labelKey: 'mail_page.folders.inbox', defaultLabel: 'Inbox', icon: Inbox },
  { id: 'drafts', labelKey: 'mail_page.folders.drafts', defaultLabel: 'Drafts', icon: FileText },
  { id: 'sent', labelKey: 'mail_page.folders.sent', defaultLabel: 'Sent', icon: Send },
  { id: 'junk', labelKey: 'mail_page.folders.junk', defaultLabel: 'Junk', icon: AlertOctagon },
  { id: 'trash', labelKey: 'mail_page.folders.trash', defaultLabel: 'Trash', icon: Trash2 },
  { id: 'archive', labelKey: 'mail_page.folders.archive', defaultLabel: 'Archive', icon: Archive },
]

const tagList: { id: MailTag; labelKey: string; defaultLabel: string; color: string }[] = [
  { id: 'work', labelKey: 'mail_page.tags.work', defaultLabel: 'Work', color: 'bg-blue-500/10 text-blue-600 border-blue-500/20' },
  { id: 'urgent', labelKey: 'mail_page.tags.urgent', defaultLabel: 'Urgent', color: 'bg-red-500/10 text-red-600 border-red-500/20' },
  { id: 'clients', labelKey: 'mail_page.tags.clients', defaultLabel: 'Clients', color: 'bg-amber-500/10 text-amber-600 border-amber-500/20' },
  { id: 'updates', labelKey: 'mail_page.tags.updates', defaultLabel: 'Updates', color: 'bg-purple-500/10 text-purple-600 border-purple-500/20' },
  { id: 'personal', labelKey: 'mail_page.tags.personal', defaultLabel: 'Personal', color: 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20' },
]

const initialMessages: MailMessage[] = [
  {
    id: 'mail-1',
    from: { name: 'Eddie Lake', email: 'eddie.lake@acme.com', initials: 'EL' },
    to: 'user@acme.com',
    subject: 'Q4 Product Roadmap & Sprint Prioritization',
    snippet: 'Hey team, I updated our deliverables for the upcoming release. Please review the updated milestones before tomorrow’s review session...',
    body: `Hi team,

I've updated our deliverables and priority queues for the upcoming release. As discussed, our main focus will be finalizing the Zero-Shot AI Classification engine and modernizing our layout components with shadcn-vue.

Key Milestones:
1. Complete Kanban integration with optimistic UI updates.
2. Polish Mail layout with three-pane responsiveness and instant reply capabilities.
3. Deploy AI PII Detection sandbox with live inference benchmarking.

Please take a moment to review the attached roadmap document and leave comments directly in the board. Let's make this sprint our smoothest one yet!

Best regards,
Eddie Lake
Head of Product Architecture`,
    date: 'Oct 14',
    timestamp: '10:42 AM',
    read: false,
    starred: true,
    folder: 'inbox',
    tags: ['work', 'urgent'],
    attachments: [
      { name: 'Roadmap_Q4_Final.pdf', size: '2.4 MB', type: 'application/pdf' },
      { name: 'Sprint_Targets.xlsx', size: '420 KB', type: 'application/vnd.ms-excel' },
    ],
  },
  {
    id: 'mail-2',
    from: { name: 'Sarah Lin', email: 'sarah.lin@security.io', initials: 'SL' },
    to: 'user@acme.com',
    subject: 'Security Audit: PII Masking & Classification Guardrails',
    snippet: 'The preliminary penetration testing report is ready. All customer tokens and financial credentials passed our zero-leakage threshold...',
    body: `Hello,

We have finished the preliminary evaluation of your content classification and data loss prevention (DLP) pipelines.

Summary Findings:
- Zero data leakage detected across standard OAuth tokens and JWT refresh endpoints.
- High classification confidence (98.4%) achieved on confidential financial data.
- Recommendation: Add automated quarantine alerts for credit card and social security number patterns in user-submitted documents.

Full assessment details and remediation suggestions are available in our compliance portal.

Thanks,
Sarah Lin
Senior Security Architect`,
    date: 'Oct 13',
    timestamp: '3:15 PM',
    read: false,
    starred: false,
    folder: 'inbox',
    tags: ['work', 'urgent'],
  },
  {
    id: 'mail-3',
    from: { name: 'Alex Morgan', email: 'alex@designstudio.co', initials: 'AM' },
    to: 'user@acme.com',
    subject: 'Updated UI Design Tokens for Tailwind v4',
    snippet: 'Here are the revised HSL color tokens and typography configurations for dark and light modes according to modern guidelines...',
    body: `Hi there,

Attached are the updated design tokens for Tailwind CSS v4 and shadcn-vue. We've fine-tuned the contrast ratios for dark mode cards, badges, and active navigation indicators to ensure seamless WCAG AAA compliance.

Highlights:
- Harmonized sidebar border transitions.
- Refined badge color opacities with 10% background tint and 20% border stroke.
- RTL typography mirroring verified for Arabic locales.

Let me know if you need any additional exports or icon sets!

Cheers,
Alex Morgan`,
    date: 'Oct 12',
    timestamp: '11:20 AM',
    read: true,
    starred: true,
    folder: 'inbox',
    tags: ['work', 'updates'],
    attachments: [
      { name: 'tokens-v4-spec.json', size: '18 KB', type: 'application/json' },
    ],
  },
  {
    id: 'mail-4',
    from: { name: 'Billing Notifications', email: 'no-reply@cloudprovider.com', initials: 'BN' },
    to: 'user@acme.com',
    subject: 'Monthly Cloud Infrastructure Invoice - September 2026',
    snippet: 'Your invoice for the period of Sept 1 - Sept 30 is now available. Your automatic payment was processed successfully...',
    body: `Dear Customer,

Your invoice for the previous month's cloud usage has been generated.

Account ID: ACME-CLOUD-8942
Total Amount: $1,420.50 USD
Payment Status: Paid (Visa ending in 4242)

You can download tax invoices and compute breakdown summaries directly from your account billing portal.

Thank you for your business!`,
    date: 'Oct 10',
    timestamp: '9:00 AM',
    read: true,
    starred: false,
    folder: 'archive',
    tags: ['clients', 'updates'],
  },
  {
    id: 'mail-5',
    from: { name: 'Jamik Tashpulatov', email: 'jamik@acme.com', initials: 'JT' },
    to: 'user@acme.com',
    subject: 'Draft: Sanctum Token Refresh Architecture',
    snippet: 'Saving notes on token rotation mechanisms and handling simultaneous browser tab requests without race conditions...',
    body: `Notes on Sanctum Refresh Flow:
- Maintain httpOnly cookie for session persistence.
- Add refresh queue interceptor in front-end client plugin to prevent multiple parallel refresh calls.
- Emit user-logged-out event upon 401 unauthenticated response.`,
    date: 'Oct 09',
    timestamp: '4:45 PM',
    read: true,
    starred: false,
    folder: 'drafts',
    tags: ['work'],
  },
]

const messages = ref<MailMessage[]>(initialMessages)
const selectedFolder = ref<FolderId>('inbox')
const selectedTag = ref<MailTag | null>(null)
const selectedMailId = ref<string>(initialMessages[0].id)
const mailFilterTab = ref<'all' | 'unread'>('all')
const searchQuery = ref('')
const quickReplyText = ref('')
const isComposeOpen = ref(false)
const showMobileDetail = ref(false)

// Compose form
const composeForm = ref({
  to: '',
  subject: '',
  category: 'work' as MailTag,
  body: '',
})

// Counts
const unreadCounts = computed(() => {
  const counts: Record<string, number> = {}
  folders.forEach((f) => {
    counts[f.id] = messages.value.filter((m) => m.folder === f.id && !m.read).length
  })
  return counts
})

// Filtered mail list
const filteredMessages = computed(() => {
  return messages.value.filter((mail) => {
    // Folder filter (or tag filter if selected)
    let matchFolderOrTag = true
    if (selectedTag.value) {
      matchFolderOrTag = mail.tags.includes(selectedTag.value)
    } else {
      matchFolderOrTag = mail.folder === selectedFolder.value
    }

    // Read/Unread tab filter
    let matchTab = true
    if (mailFilterTab.value === 'unread') {
      matchTab = !mail.read
    }

    // Search query
    let matchSearch = true
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase()
      matchSearch =
        mail.from.name.toLowerCase().includes(q) ||
        mail.from.email.toLowerCase().includes(q) ||
        mail.subject.toLowerCase().includes(q) ||
        mail.snippet.toLowerCase().includes(q) ||
        mail.body.toLowerCase().includes(q)
    }

    return matchFolderOrTag && matchTab && matchSearch
  })
})

const selectedMail = computed(() => {
  return messages.value.find((m) => m.id === selectedMailId.value) || filteredMessages.value[0] || null
})

function selectMail(mail: MailMessage) {
  selectedMailId.value = mail.id
  mail.read = true
  showMobileDetail.value = true
}

function toggleStar(mail: MailMessage) {
  mail.starred = !mail.starred
  toast.info(mail.starred ? 'Marked as starred' : 'Removed from starred')
}

function toggleRead(mail: MailMessage) {
  mail.read = !mail.read
  toast.info(mail.read ? 'Marked as read' : 'Marked as unread')
}

function moveToFolder(mail: MailMessage, targetFolder: FolderId) {
  mail.folder = targetFolder
  toast.success(`Message moved to ${targetFolder}`)
  // Select next message if available
  const remaining = filteredMessages.value.filter((m) => m.id !== mail.id)
  if (remaining.length > 0) {
    selectedMailId.value = remaining[0].id
  }
}

function sendQuickReply() {
  if (!quickReplyText.value.trim() || !selectedMail.value) return
  toast.success(te('mail_page.quick_reply.sent_toast') ? t('mail_page.quick_reply.sent_toast') : 'Email reply has been sent successfully!')
  quickReplyText.value = ''
}

function sendCompose() {
  if (!composeForm.value.to.trim() || !composeForm.value.subject.trim()) {
    toast.error('Recipient and subject are required')
    return
  }

  const newMsg: MailMessage = {
    id: `mail-${Date.now()}`,
    from: { name: 'Current User', email: 'user@acme.com', initials: 'ME' },
    to: composeForm.value.to,
    subject: composeForm.value.subject,
    snippet: composeForm.value.body.slice(0, 90) + '...',
    body: composeForm.value.body || 'No content.',
    date: 'Just now',
    timestamp: 'Just now',
    read: true,
    starred: false,
    folder: 'sent',
    tags: [composeForm.value.category],
  }

  messages.value.unshift(newMsg)
  toast.success('Message sent successfully!')
  composeForm.value = { to: '', subject: '', category: 'work', body: '' }
  isComposeOpen.value = false
}

function getTagBadgeColor(tagId: MailTag): string {
  const item = tagList.find((t) => t.id === tagId)
  return item ? item.color : 'bg-muted text-muted-foreground'
}
</script>

<template>
  <div class="flex flex-col h-[calc(100vh-var(--header-height)-1rem)] p-3 md:p-5 overflow-hidden">
    <!-- Main Shell (3-column layout) -->
    <div class="flex flex-1 border border-border/70 rounded-2xl bg-card shadow-sm overflow-hidden">
      <!-- COLUMN 1: Folders & Tags Sidebar -->
      <div class="hidden lg:flex flex-col w-56 border-e border-border/60 bg-muted/20 p-3 shrink-0">
        <!-- Compose Button -->
        <Button
          class="w-full gap-2 shadow-sm font-semibold mb-4"
          @click="isComposeOpen = true"
        >
          <PenSquare class="size-4" />
          <span>{{ te('mail_page.compose') ? t('mail_page.compose') : 'Compose' }}</span>
        </Button>

        <!-- Mailboxes list -->
        <div class="flex flex-col gap-1">
          <button
            v-for="f in folders"
            :key="f.id"
            type="button"
            class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition-colors text-start"
            :class="[
              selectedFolder === f.id && !selectedTag
                ? 'bg-primary text-primary-foreground font-semibold shadow-xs'
                : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground'
            ]"
            @click="() => { selectedFolder = f.id; selectedTag = null }"
          >
            <div class="flex items-center gap-2.5">
              <component :is="f.icon" class="size-4" />
              <span>{{ te(f.labelKey) ? t(f.labelKey) : f.defaultLabel }}</span>
            </div>
            <span
              v-if="unreadCounts[f.id]"
              class="px-1.5 py-0.5 rounded-full text-[10px] font-mono"
              :class="selectedFolder === f.id && !selectedTag ? 'bg-primary-foreground/20 text-primary-foreground' : 'bg-primary/10 text-primary font-bold'"
            >
              {{ unreadCounts[f.id] }}
            </span>
          </button>
        </div>

        <Separator class="my-4" />

        <!-- Tags / Categories -->
        <div class="flex flex-col gap-1">
          <span class="text-[11px] font-semibold text-muted-foreground/70 uppercase tracking-wider px-3 mb-1">
            Categories
          </span>
          <button
            v-for="tag in tagList"
            :key="tag.id"
            type="button"
            class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors text-start"
            :class="[
              selectedTag === tag.id
                ? 'bg-accent text-accent-foreground font-semibold'
                : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground'
            ]"
            @click="selectedTag = tag.id"
          >
            <Tag class="size-3.5" />
            <span>{{ te(tag.labelKey) ? t(tag.labelKey) : tag.defaultLabel }}</span>
          </button>
        </div>
      </div>

      <!-- COLUMN 2: Message List Pane -->
      <div
        class="flex flex-col w-full md:w-80 lg:w-96 border-e border-border/60 shrink-0"
        :class="{ 'hidden md:flex': showMobileDetail }"
      >
        <!-- Top Toolbar: Search & Tab filters -->
        <div class="p-3 border-b border-border/60 flex flex-col gap-2.5 bg-muted/10">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold tracking-tight capitalize">
              {{ selectedTag ? selectedTag : selectedFolder }}
            </h2>
            <!-- Mobile Compose button -->
            <Button
              size="sm"
              variant="outline"
              class="lg:hidden h-8 text-xs gap-1.5"
              @click="isComposeOpen = true"
            >
              <PenSquare class="size-3.5" />
              <span>Compose</span>
            </Button>
          </div>

          <div class="relative">
            <Search class="absolute start-2.5 top-1/2 -translate-y-1/2 size-3.5 text-muted-foreground" />
            <Input
              v-model="searchQuery"
              :placeholder="te('mail_page.search_placeholder') ? t('mail_page.search_placeholder') : 'Search mail...'"
              class="ps-8 h-8 text-xs bg-background"
            />
          </div>

          <Tabs v-model="mailFilterTab" class="w-full">
            <TabsList class="grid grid-cols-2 w-full h-8">
              <TabsTrigger value="all" class="text-xs">
                {{ te('mail_page.all_mail') ? t('mail_page.all_mail') : 'All Mail' }}
              </TabsTrigger>
              <TabsTrigger value="unread" class="text-xs">
                {{ te('mail_page.unread') ? t('mail_page.unread') : 'Unread' }}
              </TabsTrigger>
            </TabsList>
          </Tabs>
        </div>

        <!-- Scrollable Mail List -->
        <div class="flex-1 overflow-y-auto divide-y divide-border/40">
          <div
            v-for="mail in filteredMessages"
            :key="mail.id"
            class="p-3 cursor-pointer transition-colors relative flex flex-col gap-1.5"
            :class="[
              selectedMail?.id === mail.id
                ? 'bg-primary/5 dark:bg-primary/10'
                : 'hover:bg-muted/40',
              !mail.read ? 'bg-muted/20' : ''
            ]"
            @click="selectMail(mail)"
          >
            <!-- Top line: Sender name & Date -->
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-1.5 overflow-hidden">
                <span
                  v-if="!mail.read"
                  class="size-2 rounded-full bg-blue-600 shrink-0"
                />
                <span
                  class="text-xs truncate"
                  :class="!mail.read ? 'font-bold text-foreground' : 'font-medium text-foreground/80'"
                >
                  {{ mail.from.name }}
                </span>
              </div>

              <div class="flex items-center gap-1 shrink-0">
                <span class="text-[11px] text-muted-foreground font-mono">
                  {{ mail.timestamp }}
                </span>
                <button
                  type="button"
                  class="p-1 rounded hover:bg-muted text-muted-foreground"
                  :class="{ 'text-amber-500 fill-amber-500': mail.starred }"
                  @click.stop="toggleStar(mail)"
                >
                  <Star class="size-3.5" :class="{ 'fill-amber-500': mail.starred }" />
                </button>
              </div>
            </div>

            <!-- Subject Line -->
            <h4
              class="text-xs truncate"
              :class="!mail.read ? 'font-semibold text-foreground' : 'text-muted-foreground'"
            >
              {{ mail.subject }}
            </h4>

            <!-- Snippet -->
            <p class="text-[11px] text-muted-foreground line-clamp-2 leading-relaxed">
              {{ mail.snippet }}
            </p>

            <!-- Tags & Attachment indicator -->
            <div class="flex items-center gap-1.5 mt-1 overflow-x-auto">
              <Badge
                v-for="tag in mail.tags"
                :key="tag"
                variant="outline"
                class="text-[9px] px-1.5 py-0 capitalize"
                :class="getTagBadgeColor(tag)"
              >
                {{ tag }}
              </Badge>
              <span
                v-if="mail.attachments && mail.attachments.length > 0"
                class="flex items-center gap-0.5 text-[10px] text-muted-foreground ms-auto"
              >
                <Paperclip class="size-3" />
                <span>{{ mail.attachments.length }}</span>
              </span>
            </div>
          </div>

          <!-- Empty list state -->
          <div
            v-if="filteredMessages.length === 0"
            class="flex flex-col items-center justify-center p-10 text-center text-muted-foreground"
          >
            <MailCheck class="size-8 mb-2 opacity-40 text-primary" />
            <p class="text-xs font-medium">No messages found</p>
            <p class="text-[11px] text-muted-foreground/70 mt-0.5">Your inbox is up to date.</p>
          </div>
        </div>
      </div>

      <!-- COLUMN 3: Reading / Detail Pane -->
      <div
        class="flex flex-col flex-1 bg-background overflow-hidden"
        :class="{ 'hidden md:flex': !showMobileDetail }"
      >
        <template v-if="selectedMail">
          <!-- Detail Toolbar -->
          <div class="flex items-center justify-between p-3 border-b border-border/60 bg-muted/10">
            <!-- Mobile Back Button -->
            <div class="flex items-center gap-1">
              <Button
                variant="ghost"
                size="icon"
                class="md:hidden size-8"
                @click="showMobileDetail = false"
              >
                <ArrowLeft class="size-4" />
                <span class="sr-only">Back</span>
              </Button>

              <Button
                variant="ghost"
                size="icon"
                class="size-8 text-muted-foreground hover:text-foreground"
                title="Archive"
                @click="moveToFolder(selectedMail, 'archive')"
              >
                <Archive class="size-4" />
                <span class="sr-only">Archive</span>
              </Button>

              <Button
                variant="ghost"
                size="icon"
                class="size-8 text-muted-foreground hover:text-foreground"
                title="Move to Junk"
                @click="moveToFolder(selectedMail, 'junk')"
              >
                <AlertOctagon class="size-4" />
                <span class="sr-only">Junk</span>
              </Button>

              <Button
                variant="ghost"
                size="icon"
                class="size-8 text-muted-foreground hover:text-destructive"
                title="Delete"
                @click="moveToFolder(selectedMail, 'trash')"
              >
                <Trash2 class="size-4" />
                <span class="sr-only">Delete</span>
              </Button>
            </div>

            <div class="flex items-center gap-1">
              <Button
                variant="ghost"
                size="icon"
                class="size-8 text-muted-foreground hover:text-foreground"
                :title="selectedMail.read ? 'Mark as unread' : 'Mark as read'"
                @click="toggleRead(selectedMail)"
              >
                <MailCheck class="size-4" />
                <span class="sr-only">Toggle read</span>
              </Button>

              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <Button variant="ghost" size="icon" class="size-8 text-muted-foreground">
                    <MoreVertical class="size-4" />
                    <span class="sr-only">More</span>
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-40">
                  <DropdownMenuItem class="text-xs cursor-pointer gap-2" @click="toggleStar(selectedMail)">
                    <Star class="size-3.5" />
                    <span>{{ selectedMail.starred ? 'Unstar' : 'Star message' }}</span>
                  </DropdownMenuItem>
                  <DropdownMenuItem class="text-xs cursor-pointer gap-2" @click="moveToFolder(selectedMail, 'archive')">
                    <Archive class="size-3.5" />
                    <span>Archive</span>
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem class="text-xs text-destructive cursor-pointer gap-2" @click="moveToFolder(selectedMail, 'trash')">
                    <Trash2 class="size-3.5" />
                    <span>Delete</span>
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>
          </div>

          <!-- Email Content Body -->
          <div class="flex-1 overflow-y-auto p-4 md:p-6 flex flex-col gap-5">
            <!-- Header: Subject & Categories -->
            <div class="flex flex-col gap-2">
              <div class="flex items-start justify-between gap-3">
                <h1 class="text-lg md:text-xl font-bold leading-snug">
                  {{ selectedMail.subject }}
                </h1>
                <div class="flex items-center gap-1 shrink-0">
                  <Badge
                    v-for="tag in selectedMail.tags"
                    :key="tag"
                    variant="outline"
                    class="capitalize text-xs"
                    :class="getTagBadgeColor(tag)"
                  >
                    {{ tag }}
                  </Badge>
                </div>
              </div>

              <!-- Sender Info Row -->
              <div class="flex items-center justify-between gap-3 pt-2">
                <div class="flex items-center gap-3">
                  <Avatar class="size-9 border border-border">
                    <AvatarFallback class="bg-primary/10 text-primary font-bold text-xs">
                      {{ selectedMail.from.initials }}
                    </AvatarFallback>
                  </Avatar>
                  <div class="flex flex-col">
                    <span class="text-sm font-semibold text-foreground">
                      {{ selectedMail.from.name }}
                    </span>
                    <span class="text-xs text-muted-foreground">
                      &lt;{{ selectedMail.from.email }}&gt;
                    </span>
                  </div>
                </div>

                <div class="text-xs text-muted-foreground text-end">
                  <p>{{ selectedMail.date }}</p>
                  <p class="font-mono text-[11px]">{{ selectedMail.timestamp }}</p>
                </div>
              </div>
            </div>

            <Separator />

            <!-- Body Message -->
            <div class="text-sm leading-relaxed whitespace-pre-line text-foreground/90 font-normal">
              {{ selectedMail.body }}
            </div>

            <!-- Attachments (if any) -->
            <div v-if="selectedMail.attachments && selectedMail.attachments.length > 0" class="mt-4 pt-4 border-t border-border/50">
              <h4 class="text-xs font-semibold text-muted-foreground mb-2 flex items-center gap-1.5">
                <Paperclip class="size-3.5" />
                <span>Attachments ({{ selectedMail.attachments.length }})</span>
              </h4>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div
                  v-for="att in selectedMail.attachments"
                  :key="att.name"
                  class="flex items-center justify-between p-2.5 rounded-lg border border-border/60 bg-muted/20 text-xs"
                >
                  <div class="flex items-center gap-2 truncate">
                    <FileText class="size-4 text-primary shrink-0" />
                    <div class="truncate">
                      <p class="font-medium truncate">{{ att.name }}</p>
                      <p class="text-[10px] text-muted-foreground">{{ att.size }}</p>
                    </div>
                  </div>
                  <Button size="icon" variant="ghost" class="size-7 shrink-0 text-muted-foreground hover:text-foreground">
                    <Download class="size-3.5" />
                    <span class="sr-only">Download</span>
                  </Button>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom: Interactive Quick Reply Area -->
          <div class="p-3 md:p-4 border-t border-border/60 bg-muted/10 flex flex-col gap-2">
            <Textarea
              v-model="quickReplyText"
              :placeholder="`Reply to ${selectedMail.from.name}...`"
              rows="3"
              class="text-xs bg-background resize-none"
            />
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-1">
                <Button size="icon" variant="ghost" class="size-7 text-muted-foreground">
                  <Paperclip class="size-3.5" />
                  <span class="sr-only">Attach file</span>
                </Button>
              </div>
              <Button size="sm" class="text-xs gap-1.5 shadow-xs" @click="sendQuickReply">
                <Send class="size-3.5" />
                <span>{{ te('mail_page.quick_reply.send') ? t('mail_page.quick_reply.send') : 'Send Reply' }}</span>
              </Button>
            </div>
          </div>
        </template>

        <div v-else class="flex flex-col items-center justify-center flex-1 p-8 text-center text-muted-foreground">
          <Inbox class="size-10 mb-2 opacity-40 text-muted-foreground" />
          <p class="text-sm font-medium">Select an email to read</p>
        </div>
      </div>
    </div>

    <!-- Compose Message Modal Dialog -->
    <Dialog v-model:open="isComposeOpen">
      <DialogContent class="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>{{ te('mail_page.compose_dialog.title') ? t('mail_page.compose_dialog.title') : 'New Message' }}</DialogTitle>
          <DialogDescription>
            Compose and send an email to your colleagues or external partners.
          </DialogDescription>
        </DialogHeader>

        <div class="flex flex-col gap-3 py-2">
          <div class="flex flex-col gap-1.5">
            <Label>{{ te('mail_page.compose_dialog.to') ? t('mail_page.compose_dialog.to') : 'To' }}</Label>
            <Input v-model="composeForm.to" placeholder="colleague@acme.com" class="h-9 text-xs" />
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div class="col-span-2 flex flex-col gap-1.5">
              <Label>{{ te('mail_page.compose_dialog.subject') ? t('mail_page.compose_dialog.subject') : 'Subject' }}</Label>
              <Input v-model="composeForm.subject" placeholder="Project Update..." class="h-9 text-xs" />
            </div>

            <div class="flex flex-col gap-1.5">
              <Label>{{ te('mail_page.compose_dialog.category') ? t('mail_page.compose_dialog.category') : 'Category' }}</Label>
              <Select v-model="composeForm.category">
                <SelectTrigger class="h-9 text-xs">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="tag in tagList" :key="tag.id" :value="tag.id">
                    {{ te(tag.labelKey) ? t(tag.labelKey) : tag.defaultLabel }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div class="flex flex-col gap-1.5">
            <Label>{{ te('mail_page.compose_dialog.body') ? t('mail_page.compose_dialog.body') : 'Message' }}</Label>
            <Textarea
              v-model="composeForm.body"
              placeholder="Type your message content here..."
              rows="6"
              class="text-xs leading-relaxed"
            />
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="isComposeOpen = false">
            {{ te('mail_page.compose_dialog.discard') ? t('mail_page.compose_dialog.discard') : 'Discard' }}
          </Button>
          <Button class="gap-1.5" @click="sendCompose">
            <Send class="size-3.5" />
            <span>{{ te('mail_page.compose_dialog.send') ? t('mail_page.compose_dialog.send') : 'Send Message' }}</span>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
