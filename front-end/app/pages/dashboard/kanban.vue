<script setup lang="ts">
import { ref, computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Progress } from '@/components/ui/progress'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
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
  Search,
  Plus,
  MoreVertical,
  Calendar,
  CheckSquare,
  AlertCircle,
  Clock,
  PlayCircle,
  Eye,
  CheckCircle2,
  Trash2,
  MoveRight,
  GripVertical,
  Sparkles,
  Filter,
} from '@lucide/vue'
import { toast } from 'vue-sonner'

definePageMeta({
  layout: 'dashboard',
  middleware: ['sanctum:auth'],
  title: 'Kanban',
})

const { t, te } = useI18n()

export type ColumnId = 'backlog' | 'in_progress' | 'in_review' | 'done'
export type PriorityLevel = 'urgent' | 'high' | 'medium' | 'low'

export interface Subtask {
  id: string
  title: string
  completed: boolean
}

export interface TaskItem {
  id: string
  title: string
  description: string
  column: ColumnId
  priority: PriorityLevel
  tag: string
  dueDate: string
  assignee: {
    name: string
    initials: string
    email: string
  }
  subtasks: Subtask[]
}

const columns: { id: ColumnId; labelKey: string; defaultLabel: string; icon: any; colorClass: string; badgeVariant: 'secondary' | 'default' | 'outline' }[] = [
  {
    id: 'backlog',
    labelKey: 'kanban_page.columns.backlog',
    defaultLabel: 'Backlog',
    icon: Clock,
    colorClass: 'text-muted-foreground border-border/70',
    badgeVariant: 'secondary',
  },
  {
    id: 'in_progress',
    labelKey: 'kanban_page.columns.in_progress',
    defaultLabel: 'In Progress',
    icon: PlayCircle,
    colorClass: 'text-blue-500 border-blue-500/20 bg-blue-500/5',
    badgeVariant: 'default',
  },
  {
    id: 'in_review',
    labelKey: 'kanban_page.columns.in_review',
    defaultLabel: 'In Review',
    icon: Eye,
    colorClass: 'text-amber-500 border-amber-500/20 bg-amber-500/5',
    badgeVariant: 'outline',
  },
  {
    id: 'done',
    labelKey: 'kanban_page.columns.done',
    defaultLabel: 'Done',
    icon: CheckCircle2,
    colorClass: 'text-emerald-500 border-emerald-500/20 bg-emerald-500/5',
    badgeVariant: 'secondary',
  },
]

const initialTasks: TaskItem[] = [
  {
    id: 'TASK-101',
    title: 'Architect Enterprise Classification Pipeline',
    description: 'Design asynchronous job queue for scanning incoming attachments and detecting sensitive PII tokens.',
    column: 'in_progress',
    priority: 'urgent',
    tag: 'AI Engine',
    dueDate: 'Oct 18',
    assignee: { name: 'Sarah Lin', initials: 'SL', email: 'sarah@acme.com' },
    subtasks: [
      { id: 'st-1', title: 'Token normalization pipeline', completed: true },
      { id: 'st-2', title: 'Vector embeddings caching', completed: true },
      { id: 'st-3', title: 'Zero-shot classification prompt testing', completed: false },
    ],
  },
  {
    id: 'TASK-102',
    title: 'Unified Mailbox & Thread Syncing',
    description: 'Build responsive 3-pane email client using shadcn resizable components and realtime notification hooks.',
    column: 'in_progress',
    priority: 'high',
    tag: 'Frontend',
    dueDate: 'Oct 20',
    assignee: { name: 'Eddie Lake', initials: 'EL', email: 'eddie@acme.com' },
    subtasks: [
      { id: 'st-4', title: 'Desktop resizable split panels', completed: true },
      { id: 'st-5', title: 'Compose modal with markdown support', completed: true },
      { id: 'st-6', title: 'Mobile collapse drawer', completed: false },
    ],
  },
  {
    id: 'TASK-103',
    title: 'Setup GDPR Data Subject Export API',
    description: 'Expose Sanctum-authenticated endpoints for user data downloads in JSON and PDF format.',
    column: 'backlog',
    priority: 'medium',
    tag: 'Backend',
    dueDate: 'Oct 28',
    assignee: { name: 'Jamik Tashpulatov', initials: 'JT', email: 'jamik@acme.com' },
    subtasks: [
      { id: 'st-7', title: 'Database schema migration', completed: false },
      { id: 'st-8', title: 'Audit trail logging', completed: false },
    ],
  },
  {
    id: 'TASK-104',
    title: 'Audit Trail and Sensitive Content Masking',
    description: 'Ensure credit card numbers and access tokens are masked in server logs and admin views.',
    column: 'in_review',
    priority: 'urgent',
    tag: 'Security',
    dueDate: 'Oct 16',
    assignee: { name: 'Sarah Lin', initials: 'SL', email: 'sarah@acme.com' },
    subtasks: [
      { id: 'st-9', title: 'Regex masking patterns', completed: true },
      { id: 'st-10', title: 'Penetration test report', completed: true },
    ],
  },
  {
    id: 'TASK-105',
    title: 'Implement Dark / Light System Synchronizer',
    description: 'Harmonize Nuxt ColorMode with shadcn-vue Tailwind CSS v4 design tokens and CSS variables.',
    column: 'done',
    priority: 'low',
    tag: 'Design',
    dueDate: 'Oct 14',
    assignee: { name: 'Alex Morgan', initials: 'AM', email: 'alex@acme.com' },
    subtasks: [
      { id: 'st-11', title: 'Theme toggle component', completed: true },
      { id: 'st-12', title: 'Contrast ratios accessibility pass', completed: true },
    ],
  },
  {
    id: 'TASK-106',
    title: 'Internationalization RTL Layout Alignment',
    description: 'Verify Arabic RTL direction across sidebars, breadcrumbs, data tables, and kanban cards.',
    column: 'done',
    priority: 'medium',
    tag: 'i18n',
    dueDate: 'Oct 13',
    assignee: { name: 'Youness B.', initials: 'YB', email: 'youness@acme.com' },
    subtasks: [
      { id: 'st-13', title: 'Mirrored grid order', completed: true },
      { id: 'st-14', title: 'Arabic typography line-height fix', completed: true },
    ],
  },
]

const tasks = ref<TaskItem[]>(initialTasks)
const searchQuery = ref('')
const priorityFilter = ref<string>('all')
const tagFilter = ref<string>('all')

// Drag & drop state
const draggedTaskId = ref<string | null>(null)
const dragOverColumn = ref<ColumnId | null>(null)

// Dialog state
const isCreateDialogOpen = ref(false)
const isDetailDialogOpen = ref(false)
const activeTask = ref<TaskItem | null>(null)

// New Task Form
const newTask = ref({
  title: '',
  description: '',
  column: 'backlog' as ColumnId,
  priority: 'medium' as PriorityLevel,
  tag: 'Frontend',
  dueDate: 'Next Week',
  assigneeName: 'Eddie Lake',
  newSubtaskTitle: '',
  subtasks: [] as Subtask[],
})

const availableTags = ['Frontend', 'Backend', 'AI Engine', 'Security', 'Design', 'i18n', 'DevOps']

const filteredTasks = computed(() => {
  return tasks.value.filter((task) => {
    const matchesSearch =
      task.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      task.description.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      task.id.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      task.assignee.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      task.tag.toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchesPriority = priorityFilter.value === 'all' || task.priority === priorityFilter.value
    const matchesTag = tagFilter.value === 'all' || task.tag === tagFilter.value

    return matchesSearch && matchesPriority && matchesTag
  })
})

function getTasksByColumn(colId: ColumnId) {
  return filteredTasks.value.filter((t) => t.column === colId)
}

// Metrics
const stats = computed(() => {
  const total = tasks.value.length
  const inProgress = tasks.value.filter((t) => t.column === 'in_progress').length
  const inReview = tasks.value.filter((t) => t.column === 'in_review').length
  const completed = tasks.value.filter((t) => t.column === 'done').length
  const progressPercent = total > 0 ? Math.round((completed / total) * 100) : 0
  return { total, inProgress, inReview, completed, progressPercent }
})

// Drag & Drop Handlers
function handleDragStart(task: TaskItem) {
  draggedTaskId.value = task.id
}

function handleDragOver(colId: ColumnId) {
  dragOverColumn.value = colId
}

function handleDragLeave(colId: ColumnId) {
  if (dragOverColumn.value === colId) {
    dragOverColumn.value = null
  }
}

function handleDrop(targetColumn: ColumnId) {
  if (!draggedTaskId.value) return
  const task = tasks.value.find((t) => t.id === draggedTaskId.value)
  if (task && task.column !== targetColumn) {
    const oldCol = task.column
    task.column = targetColumn
    toast.success(`Task moved to ${getColumnLabel(targetColumn)}`)
  }
  draggedTaskId.value = null
  dragOverColumn.value = null
}

function moveTask(task: TaskItem, targetColumn: ColumnId) {
  task.column = targetColumn
  toast.success(`Moved ${task.id} to ${getColumnLabel(targetColumn)}`)
}

function deleteTask(taskId: string) {
  tasks.value = tasks.value.filter((t) => t.id !== taskId)
  if (activeTask.value?.id === taskId) {
    isDetailDialogOpen.value = false
  }
  toast.info('Task deleted from board')
}

function openTaskDetail(task: TaskItem) {
  activeTask.value = task
  isDetailDialogOpen.value = true
}

function toggleSubtask(task: TaskItem, subtask: Subtask) {
  subtask.completed = !subtask.completed
}

function addSubtaskToNew() {
  if (!newTask.value.newSubtaskTitle.trim()) return
  newTask.value.subtasks.push({
    id: `st-${Date.now()}`,
    title: newTask.value.newSubtaskTitle.trim(),
    completed: false,
  })
  newTask.value.newSubtaskTitle = ''
}

function removeSubtaskFromNew(index: number) {
  newTask.value.subtasks.splice(index, 1)
}

function createNewTask() {
  if (!newTask.value.title.trim()) {
    toast.error('Task title is required')
    return
  }

  const id = `TASK-${Math.floor(100 + Math.random() * 900)}`
  const initials = newTask.value.assigneeName
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()

  const created: TaskItem = {
    id,
    title: newTask.value.title.trim(),
    description: newTask.value.description.trim() || 'No description provided.',
    column: newTask.value.column,
    priority: newTask.value.priority,
    tag: newTask.value.tag,
    dueDate: newTask.value.dueDate || 'Pending',
    assignee: {
      name: newTask.value.assigneeName,
      initials,
      email: `${newTask.value.assigneeName.toLowerCase().replace(/\s+/g, '.')}@acme.com`,
    },
    subtasks: [...newTask.value.subtasks],
  }

  tasks.value.unshift(created)
  toast.success(`Created task ${id}`)

  // Reset form
  newTask.value = {
    title: '',
    description: '',
    column: 'backlog',
    priority: 'medium',
    tag: 'Frontend',
    dueDate: 'Next Week',
    assigneeName: 'Eddie Lake',
    newSubtaskTitle: '',
    subtasks: [],
  }
  isCreateDialogOpen.value = false
}

function getColumnLabel(id: ColumnId): string {
  const col = columns.find((c) => c.id === id)
  if (!col) return id
  return te(col.labelKey) ? t(col.labelKey) : col.defaultLabel
}

function getPriorityBadgeClass(priority: PriorityLevel) {
  switch (priority) {
    case 'urgent':
      return 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20'
    case 'high':
      return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20'
    case 'medium':
      return 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20'
    case 'low':
      return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'
  }
}
</script>

<template>
  <div class="flex flex-col gap-5 p-4 md:p-6 lg:p-8">
    <!-- Header with Breadcrumb / Title & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
          {{ te('kanban_page.title') ? t('kanban_page.title') : 'Kanban Board' }}
        </h1>
        <p class="text-sm text-muted-foreground mt-1">
          {{ te('kanban_page.description') ? t('kanban_page.description') : 'Streamline workflow stages, manage agile tasks, and collaborate in real-time.' }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <Button class="gap-2 shadow-sm" @click="isCreateDialogOpen = true">
          <Plus class="size-4" />
          <span>{{ te('kanban_page.new_task') ? t('kanban_page.new_task') : 'New Task' }}</span>
        </Button>
      </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
      <Card class="border-border/60 shadow-xs">
        <CardContent class="p-4 flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-muted-foreground">
              {{ te('kanban_page.total_tasks') ? t('kanban_page.total_tasks') : 'Total Tasks' }}
            </p>
            <p class="text-2xl font-bold mt-1">{{ stats.total }}</p>
          </div>
          <div class="p-2.5 rounded-xl bg-primary/10 text-primary">
            <CheckSquare class="size-5" />
          </div>
        </CardContent>
      </Card>

      <Card class="border-border/60 shadow-xs">
        <CardContent class="p-4 flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-muted-foreground">
              {{ te('kanban_page.in_progress') ? t('kanban_page.in_progress') : 'In Progress' }}
            </p>
            <p class="text-2xl font-bold mt-1 text-blue-600 dark:text-blue-400">{{ stats.inProgress }}</p>
          </div>
          <div class="p-2.5 rounded-xl bg-blue-500/10 text-blue-500">
            <PlayCircle class="size-5" />
          </div>
        </CardContent>
      </Card>

      <Card class="border-border/60 shadow-xs">
        <CardContent class="p-4 flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-muted-foreground">
              {{ te('kanban_page.in_review') ? t('kanban_page.in_review') : 'Under Review' }}
            </p>
            <p class="text-2xl font-bold mt-1 text-amber-600 dark:text-amber-400">{{ stats.inReview }}</p>
          </div>
          <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-500">
            <Eye class="size-5" />
          </div>
        </CardContent>
      </Card>

      <Card class="border-border/60 shadow-xs">
        <CardContent class="p-4 flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-muted-foreground">
              {{ te('kanban_page.completed') ? t('kanban_page.completed') : 'Completed' }}
            </p>
            <div class="flex items-center gap-2 mt-1">
              <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.completed }}</span>
              <span class="text-xs text-muted-foreground font-medium">({{ stats.progressPercent }}%)</span>
            </div>
          </div>
          <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-500">
            <CheckCircle2 class="size-5" />
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-muted/40 p-2.5 rounded-xl border border-border/50">
      <div class="relative flex-1 max-w-md">
        <Search class="absolute start-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
        <Input
          v-model="searchQuery"
          :placeholder="te('kanban_page.search_placeholder') ? t('kanban_page.search_placeholder') : 'Search tasks by title, tag, or assignee...'"
          class="ps-9 h-9 bg-background border-border/60"
        />
      </div>

      <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
        <!-- Priority Filter -->
        <Select v-model="priorityFilter">
          <SelectTrigger class="h-9 w-[140px] text-xs bg-background">
            <SelectValue placeholder="Priority" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Priorities</SelectItem>
            <SelectItem value="urgent">Urgent</SelectItem>
            <SelectItem value="high">High</SelectItem>
            <SelectItem value="medium">Medium</SelectItem>
            <SelectItem value="low">Low</SelectItem>
          </SelectContent>
        </Select>

        <!-- Tag Filter -->
        <Select v-model="tagFilter">
          <SelectTrigger class="h-9 w-[130px] text-xs bg-background">
            <SelectValue placeholder="Tag" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Tags</SelectItem>
            <SelectItem v-for="tag in availableTags" :key="tag" :value="tag">
              {{ tag }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>

    <!-- Kanban Columns Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 items-start">
      <div
        v-for="col in columns"
        :key="col.id"
        class="flex flex-col rounded-xl border bg-muted/20 transition-colors p-3"
        :class="[
          dragOverColumn === col.id
            ? 'border-primary ring-2 ring-primary/20 bg-primary/5'
            : 'border-border/60'
        ]"
        @dragover.prevent="handleDragOver(col.id)"
        @dragleave="handleDragLeave(col.id)"
        @drop="handleDrop(col.id)"
      >
        <!-- Column Header -->
        <div class="flex items-center justify-between pb-3 px-1">
          <div class="flex items-center gap-2">
            <component :is="col.icon" class="size-4" :class="col.colorClass" />
            <h2 class="font-semibold text-sm">
              {{ te(col.labelKey) ? t(col.labelKey) : col.defaultLabel }}
            </h2>
            <Badge variant="secondary" class="h-5 px-1.5 text-xs font-mono rounded-full">
              {{ getTasksByColumn(col.id).length }}
            </Badge>
          </div>

          <Button
            size="icon"
            variant="ghost"
            class="size-7 text-muted-foreground hover:text-foreground"
            @click="() => { newTask.column = col.id; isCreateDialogOpen = true }"
          >
            <Plus class="size-3.5" />
            <span class="sr-only">Add Task</span>
          </Button>
        </div>

        <!-- Task Cards Container -->
        <div class="flex flex-col gap-2.5 min-h-[350px]">
          <div
            v-for="task in getTasksByColumn(col.id)"
            :key="task.id"
            draggable="true"
            class="group cursor-grab active:cursor-grabbing rounded-lg border border-border/70 bg-card p-3.5 shadow-xs transition-all hover:border-border hover:shadow-md select-none"
            @dragstart="handleDragStart(task)"
            @click="openTaskDetail(task)"
          >
            <!-- Card Header: ID, Tag & Menu -->
            <div class="flex items-center justify-between gap-2 mb-2">
              <div class="flex items-center gap-1.5">
                <span class="font-mono text-[11px] font-semibold text-muted-foreground">
                  {{ task.id }}
                </span>
                <Badge variant="outline" class="text-[10px] px-1.5 py-0 font-normal">
                  {{ task.tag }}
                </Badge>
              </div>

              <div class="flex items-center gap-1" @click.stop>
                <Badge
                  variant="outline"
                  class="text-[10px] px-1.5 py-0 capitalize font-medium"
                  :class="getPriorityBadgeClass(task.priority)"
                >
                  {{ task.priority }}
                </Badge>

                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="size-6 text-muted-foreground opacity-60 group-hover:opacity-100 hover:text-foreground"
                    >
                      <MoreVertical class="size-3" />
                      <span class="sr-only">Menu</span>
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-44">
                    <DropdownMenuLabel class="text-xs">Quick Move</DropdownMenuLabel>
                    <DropdownMenuItem
                      v-for="targetCol in columns.filter(c => c.id !== task.column)"
                      :key="targetCol.id"
                      class="text-xs cursor-pointer gap-2"
                      @click="moveTask(task, targetCol.id)"
                    >
                      <MoveRight class="size-3 text-muted-foreground" />
                      <span>{{ te(targetCol.labelKey) ? t(targetCol.labelKey) : targetCol.defaultLabel }}</span>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                      class="text-xs text-destructive focus:text-destructive cursor-pointer gap-2"
                      @click="deleteTask(task.id)"
                    >
                      <Trash2 class="size-3" />
                      <span>Delete</span>
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </div>

            <!-- Title & Description -->
            <h3 class="text-sm font-semibold leading-snug line-clamp-2 group-hover:text-primary transition-colors">
              {{ task.title }}
            </h3>
            <p class="text-xs text-muted-foreground line-clamp-2 mt-1">
              {{ task.description }}
            </p>

            <!-- Subtasks Progress (if any) -->
            <div v-if="task.subtasks.length > 0" class="mt-3">
              <div class="flex items-center justify-between text-[11px] text-muted-foreground mb-1">
                <span class="flex items-center gap-1">
                  <CheckSquare class="size-3" />
                  {{ task.subtasks.filter(s => s.completed).length }}/{{ task.subtasks.length }}
                </span>
                <span>
                  {{ Math.round((task.subtasks.filter(s => s.completed).length / task.subtasks.length) * 100) }}%
                </span>
              </div>
              <Progress
                :model-value="(task.subtasks.filter(s => s.completed).length / task.subtasks.length) * 100"
                class="h-1 bg-muted"
              />
            </div>

            <!-- Footer: Due Date & Assignee -->
            <div class="flex items-center justify-between gap-2 mt-3 pt-2.5 border-t border-border/40 text-xs text-muted-foreground">
              <div class="flex items-center gap-1 text-[11px]">
                <Calendar class="size-3" />
                <span>{{ task.dueDate }}</span>
              </div>

              <div class="flex items-center gap-1.5">
                <span class="text-[11px] font-medium hidden sm:inline">
                  {{ task.assignee.name.split(' ')[0] }}
                </span>
                <Avatar class="size-5 text-[10px] border border-border">
                  <AvatarFallback class="bg-primary/10 text-primary font-bold">
                    {{ task.assignee.initials }}
                  </AvatarFallback>
                </Avatar>
              </div>
            </div>
          </div>

          <!-- Empty State in Column -->
          <div
            v-if="getTasksByColumn(col.id).length === 0"
            class="flex flex-col items-center justify-center p-8 rounded-lg border border-dashed border-border/50 text-muted-foreground text-xs text-center"
          >
            <component :is="col.icon" class="size-5 mb-1.5 opacity-40" />
            <span>No tasks in this stage</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Task Dialog -->
    <Dialog v-model:open="isCreateDialogOpen">
      <DialogContent class="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>{{ te('kanban_page.dialog.create_title') ? t('kanban_page.dialog.create_title') : 'Create New Task' }}</DialogTitle>
          <DialogDescription>
            {{ te('kanban_page.dialog.create_desc') ? t('kanban_page.dialog.create_desc') : 'Add a new task card to your workflow board.' }}
          </DialogDescription>
        </DialogHeader>

        <div class="flex flex-col gap-3.5 py-2">
          <!-- Title -->
          <div class="flex flex-col gap-1.5">
            <Label for="task-title">{{ te('kanban_page.dialog.title_label') ? t('kanban_page.dialog.title_label') : 'Title' }}</Label>
            <Input
              id="task-title"
              v-model="newTask.title"
              placeholder="e.g. Implement Zero-Shot Classifier"
              class="h-9"
            />
          </div>

          <!-- Column & Priority -->
          <div class="grid grid-cols-2 gap-3">
            <div class="flex flex-col gap-1.5">
              <Label>{{ te('kanban_page.dialog.column_label') ? t('kanban_page.dialog.column_label') : 'Column' }}</Label>
              <Select v-model="newTask.column">
                <SelectTrigger class="h-9">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="col in columns" :key="col.id" :value="col.id">
                    {{ te(col.labelKey) ? t(col.labelKey) : col.defaultLabel }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="flex flex-col gap-1.5">
              <Label>{{ te('kanban_page.dialog.priority_label') ? t('kanban_page.dialog.priority_label') : 'Priority' }}</Label>
              <Select v-model="newTask.priority">
                <SelectTrigger class="h-9">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="urgent">Urgent</SelectItem>
                  <SelectItem value="high">High</SelectItem>
                  <SelectItem value="medium">Medium</SelectItem>
                  <SelectItem value="low">Low</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <!-- Tag & Due Date -->
          <div class="grid grid-cols-2 gap-3">
            <div class="flex flex-col gap-1.5">
              <Label>{{ te('kanban_page.dialog.label_label') ? t('kanban_page.dialog.label_label') : 'Tag' }}</Label>
              <Select v-model="newTask.tag">
                <SelectTrigger class="h-9">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="tag in availableTags" :key="tag" :value="tag">
                    {{ tag }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="flex flex-col gap-1.5">
              <Label>{{ te('kanban_page.dialog.due_label') ? t('kanban_page.dialog.due_label') : 'Due Date' }}</Label>
              <Input v-model="newTask.dueDate" placeholder="e.g. Oct 25" class="h-9" />
            </div>
          </div>

          <!-- Description -->
          <div class="flex flex-col gap-1.5">
            <Label for="task-desc">{{ te('kanban_page.dialog.desc_label') ? t('kanban_page.dialog.desc_label') : 'Description' }}</Label>
            <Textarea
              id="task-desc"
              v-model="newTask.description"
              placeholder="Outline acceptance criteria, deliverables, and architecture notes..."
              rows="3"
            />
          </div>

          <!-- Subtasks -->
          <div class="flex flex-col gap-2">
            <Label>{{ te('kanban_page.dialog.subtasks_label') ? t('kanban_page.dialog.subtasks_label') : 'Subtasks' }}</Label>
            <div class="flex items-center gap-2">
              <Input
                v-model="newTask.newSubtaskTitle"
                placeholder="Add subtask item..."
                class="h-8 text-xs"
                @keydown.enter.prevent="addSubtaskToNew"
              />
              <Button size="sm" variant="secondary" class="h-8 text-xs" @click="addSubtaskToNew">
                {{ te('kanban_page.dialog.add_subtask') ? t('kanban_page.dialog.add_subtask') : 'Add' }}
              </Button>
            </div>

            <div v-if="newTask.subtasks.length > 0" class="flex flex-col gap-1 mt-1 max-h-32 overflow-y-auto">
              <div
                v-for="(st, idx) in newTask.subtasks"
                :key="st.id"
                class="flex items-center justify-between text-xs bg-muted/40 px-2 py-1.5 rounded border border-border/40"
              >
                <span>{{ st.title }}</span>
                <button
                  type="button"
                  class="text-muted-foreground hover:text-destructive"
                  @click="removeSubtaskFromNew(idx)"
                >
                  ×
                </button>
              </div>
            </div>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="isCreateDialogOpen = false">
            {{ $t('common.cancel') }}
          </Button>
          <Button @click="createNewTask">
            {{ te('kanban_page.dialog.create') ? t('kanban_page.dialog.create') : 'Create Task' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Task Detail / Interactive Subtask Modal -->
    <Dialog v-model:open="isDetailDialogOpen">
      <DialogContent v-if="activeTask" class="sm:max-w-lg">
        <DialogHeader>
          <div class="flex items-center justify-between gap-2 me-4">
            <span class="font-mono text-xs font-bold text-muted-foreground">{{ activeTask.id }}</span>
            <Badge
              variant="outline"
              class="capitalize text-xs"
              :class="getPriorityBadgeClass(activeTask.priority)"
            >
              {{ activeTask.priority }}
            </Badge>
          </div>
          <DialogTitle class="text-xl mt-1">{{ activeTask.title }}</DialogTitle>
          <DialogDescription class="text-xs">
            Assigned to {{ activeTask.assignee.name }} · Due {{ activeTask.dueDate }}
          </DialogDescription>
        </DialogHeader>

        <div class="flex flex-col gap-4 py-2">
          <!-- Description -->
          <div>
            <h4 class="text-xs font-semibold text-muted-foreground mb-1">Description</h4>
            <p class="text-sm leading-relaxed bg-muted/30 p-3 rounded-lg border border-border/50">
              {{ activeTask.description }}
            </p>
          </div>

          <!-- Stage Selection -->
          <div class="flex items-center justify-between bg-muted/20 p-2.5 rounded-lg border border-border/40">
            <span class="text-xs font-medium text-muted-foreground">Current Stage:</span>
            <Select
              :model-value="activeTask.column"
              @update:model-value="(val) => moveTask(activeTask!, val as ColumnId)"
            >
              <SelectTrigger class="h-8 w-40 text-xs">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="col in columns" :key="col.id" :value="col.id">
                  {{ te(col.labelKey) ? t(col.labelKey) : col.defaultLabel }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Interactive Subtasks List -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <h4 class="text-xs font-semibold text-muted-foreground">Subtasks Checklist</h4>
              <span class="text-xs text-muted-foreground font-mono">
                {{ activeTask.subtasks.filter(s => s.completed).length }}/{{ activeTask.subtasks.length }}
              </span>
            </div>

            <div class="flex flex-col gap-1.5">
              <div
                v-for="st in activeTask.subtasks"
                :key="st.id"
                class="flex items-center gap-2 p-2 rounded-lg border border-border/40 hover:bg-muted/40 cursor-pointer transition-colors"
                @click="toggleSubtask(activeTask, st)"
              >
                <div
                  class="size-4 rounded border flex items-center justify-center transition-colors"
                  :class="st.completed ? 'bg-primary border-primary text-primary-foreground' : 'border-muted-foreground/50'"
                >
                  <CheckCircle2 v-if="st.completed" class="size-3" />
                </div>
                <span
                  class="text-xs flex-1 transition-all"
                  :class="st.completed ? 'line-through text-muted-foreground' : 'text-foreground font-medium'"
                >
                  {{ st.title }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <DialogFooter class="flex sm:justify-between items-center gap-2">
          <Button
            variant="destructive"
            size="sm"
            class="text-xs gap-1.5"
            @click="deleteTask(activeTask.id)"
          >
            <Trash2 class="size-3.5" />
            <span>{{ te('kanban_page.dialog.delete_task') ? t('kanban_page.dialog.delete_task') : 'Delete Task' }}</span>
          </Button>

          <Button size="sm" @click="isDetailDialogOpen = false">
            {{ $t('common.close') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
