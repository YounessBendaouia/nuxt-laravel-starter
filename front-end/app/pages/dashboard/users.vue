<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import {
  Search,
  MoreHorizontal,
  Shield,
  Trash2,
  Edit2,
  UserPlus,
} from '@lucide/vue'
import { toast } from 'vue-sonner'

definePageMeta({
  layout: 'dashboard',
  middleware: ['sanctum:auth'],
  title: 'Users',
})

const { t, locale } = useI18n()
const client = useSanctumClient()
const searchQuery = ref('')
const isAddUserOpen = ref(false)

interface AppUser {
  id: number
  name: string
  email: string
  role: 'Admin' | 'Member' | 'Manager'
  status: 'active' | 'pending'
  created_at: string
}

const usersList = ref<AppUser[]>([
  { id: 1, name: 'Alex Johnson', email: 'alex@example.com', role: 'Admin', status: 'active', created_at: '2026-09-01T10:00:00Z' },
  { id: 2, name: 'Sarah Miller', email: 'sarah@example.com', role: 'Manager', status: 'active', created_at: '2026-09-05T14:30:00Z' },
  { id: 3, name: 'David Chen', email: 'david@example.com', role: 'Member', status: 'active', created_at: '2026-09-08T09:15:00Z' },
  { id: 4, name: 'Emma Wilson', email: 'emma@example.com', role: 'Member', status: 'pending', created_at: '2026-09-11T16:45:00Z' },
  { id: 5, name: 'James Taylor', email: 'james@example.com', role: 'Member', status: 'active', created_at: '2026-09-13T11:20:00Z' },
])

onMounted(async () => {
  try {
    const stats = await client<any>('/api/dashboard/stats')
    if (stats?.recent_users?.length) {
      stats.recent_users.forEach((u: any) => {
        if (!usersList.value.some(existing => existing.email === u.email)) {
          usersList.value.unshift({
            id: u.id,
            name: u.name,
            email: u.email,
            role: 'Member',
            status: 'active',
            created_at: u.created_at || new Date().toISOString(),
          })
        }
      })
    }
  } catch (e) {
    // Graceful offline fallback
  }
})

const filteredUsers = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()
  if (!query) return usersList.value
  return usersList.value.filter(
    (u) =>
      u.name.toLowerCase().includes(query) ||
      u.email.toLowerCase().includes(query) ||
      u.role.toLowerCase().includes(query)
  )
})

const newUserForm = reactive({
  name: '',
  email: '',
  role: 'Member' as 'Admin' | 'Member' | 'Manager',
})

function handleAddUser() {
  if (!newUserForm.name || !newUserForm.email) return

  const newId = Math.max(...usersList.value.map((u) => u.id), 0) + 1
  usersList.value.unshift({
    id: newId,
    name: newUserForm.name,
    email: newUserForm.email,
    role: newUserForm.role,
    status: 'active',
    created_at: new Date().toISOString(),
  })

  toast.success(t('users_page.toast_invited', { name: newUserForm.name }))
  newUserForm.name = ''
  newUserForm.email = ''
  newUserForm.role = 'Member'
  isAddUserOpen.value = false
}

function handleDeleteUser(user: AppUser) {
  usersList.value = usersList.value.filter((u) => u.id !== user.id)
  toast.info(t('users_page.toast_removed', { name: user.name }))
}
</script>

<template>
  <div class="flex flex-col gap-4 py-4 md:gap-6 md:py-6 px-4 lg:px-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight md:text-3xl">{{ t('users_page.title') }}</h1>
        <p class="text-sm text-muted-foreground">
          {{ t('users_page.description') }}
        </p>
      </div>

      <!-- Add User Dialog -->
      <Dialog v-model:open="isAddUserOpen">
        <DialogTrigger as-child>
          <Button class="gap-2">
            <UserPlus class="h-4 w-4" />
            {{ t('users_page.add_user') }}
          </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>{{ t('users_page.dialog_title') }}</DialogTitle>
            <DialogDescription>
              {{ t('users_page.dialog_description') }}
            </DialogDescription>
          </DialogHeader>

          <form class="space-y-4 py-2" @submit.prevent="handleAddUser">
            <div class="space-y-2">
              <Label for="newUserName">{{ t('users_page.full_name') }}</Label>
              <Input id="newUserName" v-model="newUserForm.name" placeholder="John Doe" required />
            </div>

            <div class="space-y-2">
              <Label for="newUserEmail">{{ t('users_page.email_address') }}</Label>
              <Input id="newUserEmail" v-model="newUserForm.email" type="email" placeholder="john@example.com" required />
            </div>

            <div class="space-y-2">
              <Label for="newUserRole">{{ t('users_page.role') }}</Label>
              <select
                id="newUserRole"
                v-model="newUserForm.role"
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
              >
                <option value="Member">{{ t('users_page.role_member') }}</option>
                <option value="Manager">{{ t('users_page.role_manager') }}</option>
                <option value="Admin">{{ t('users_page.role_admin') }}</option>
              </select>
            </div>

            <DialogFooter class="pt-4">
              <Button type="button" variant="outline" @click="isAddUserOpen = false">
                {{ t('common.cancel') }}
              </Button>
              <Button type="submit">
                {{ t('users_page.send_invitation') }}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </div>

    <!-- Filter & Table Card -->
    <Card class="border-border/60 shadow-sm">
      <CardHeader class="pb-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <CardTitle class="text-lg font-semibold">{{ t('users_page.all_users', { count: filteredUsers.length }) }}</CardTitle>
            <CardDescription class="text-xs">{{ t('users_page.realtime_records') }}</CardDescription>
          </div>

          <div class="relative w-full sm:w-72">
            <Search class="absolute start-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input
              v-model="searchQuery"
              :placeholder="t('users_page.search_placeholder')"
              class="ps-9 h-9 text-xs"
            />
          </div>
        </div>
      </CardHeader>

      <CardContent class="p-0">
        <Table>
          <TableHeader>
            <TableRow class="hover:bg-transparent">
              <TableHead class="w-[80px]">{{ t('users_page.col_id') }}</TableHead>
              <TableHead>{{ t('users_page.col_user') }}</TableHead>
              <TableHead>{{ t('users_page.col_role') }}</TableHead>
              <TableHead>{{ t('users_page.col_status') }}</TableHead>
              <TableHead>{{ t('users_page.col_joined') }}</TableHead>
              <TableHead class="text-end w-[80px]">{{ t('users_page.col_actions') }}</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="user in filteredUsers"
              :key="user.id"
              class="hover:bg-muted/50 transition-colors"
            >
              <TableCell class="font-mono text-xs text-muted-foreground font-semibold">
                #{{ user.id }}
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-3">
                  <Avatar class="h-8 w-8 border border-border/50">
                    <AvatarFallback class="text-xs font-semibold bg-primary/10 text-primary">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </AvatarFallback>
                  </Avatar>
                  <div>
                    <div class="font-medium text-sm text-foreground">{{ user.name }}</div>
                    <div class="text-xs text-muted-foreground">{{ user.email }}</div>
                  </div>
                </div>
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-1.5 text-xs font-medium">
                  <Shield class="h-3.5 w-3.5 text-muted-foreground" />
                  {{ user.role === 'Admin' ? t('users_page.role_admin') : user.role === 'Manager' ? t('users_page.role_manager') : t('users_page.role_member') }}
                </div>
              </TableCell>
              <TableCell>
                <Badge
                  :variant="user.status === 'active' ? 'default' : 'secondary'"
                  class="text-[11px] font-medium capitalize"
                  :class="user.status === 'active' ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20' : ''"
                >
                  {{ user.status === 'active' ? t('users_page.status_active') : t('users_page.status_pending') }}
                </Badge>
              </TableCell>
              <TableCell class="text-xs text-muted-foreground">
                {{ new Date(user.created_at).toLocaleDateString(locale) }}
              </TableCell>
              <TableCell class="text-end">
                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button variant="ghost" size="icon" class="h-8 w-8">
                      <MoreHorizontal class="h-4 w-4" />
                      <span class="sr-only">{{ t('users_page.col_actions') }}</span>
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-40">
                    <DropdownMenuItem class="cursor-pointer gap-2" @click="toast.info(t('users_page.toast_editing', { name: user.name }))">
                      <Edit2 class="h-3.5 w-3.5" />
                      {{ t('users_page.edit_details') }}
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                      class="cursor-pointer gap-2 text-destructive focus:text-destructive focus:bg-destructive/10"
                      @click="handleDeleteUser(user)"
                    >
                      <Trash2 class="h-3.5 w-3.5" />
                      {{ t('users_page.delete_user') }}
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </TableCell>
            </TableRow>

            <TableRow v-if="filteredUsers.length === 0">
              <TableCell colspan="6" class="text-center py-12 text-sm text-muted-foreground">
                {{ t('users_page.no_users_found', { query: searchQuery }) }}
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  </div>
</template>
