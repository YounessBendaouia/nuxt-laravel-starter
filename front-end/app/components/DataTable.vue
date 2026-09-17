<script lang="ts">
import { z } from "zod"
import DraggableRow from "./DraggableRow.vue"
import DragHandle from "./DragHandle.vue"

export const schema = z.object({
  id: z.number(),
  header: z.string(),
  type: z.string(),
  status: z.string(),
  target: z.string(),
  limit: z.string(),
  reviewer: z.string(),
})
</script>

<script setup lang="ts">
import { ref, h } from "vue"
import type { RowSelectionState } from "@tanstack/vue-table"
import { RestrictToVerticalAxis } from "@dnd-kit/abstract/modifiers"
import {
  IconChevronDown,
  IconChevronLeft,
  IconChevronRight,
  IconChevronsLeft,
  IconChevronsRight,
  IconCircleCheckFilled,
  IconDotsVertical,
  IconLayoutColumns,
  IconLoader,
  IconPlus,
} from "@tabler/icons-vue"
import {
  createColumnHelper,
  FlexRender,
  useTable,
} from "@tanstack/vue-table"
import { DragDropProvider } from "dnd-kit-vue"
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from '@/components/ui/tabs'
import { features } from "./features"

const props = defineProps<{
  data: TableData[]
}>()

const { t } = useI18n()

interface TableData {
  id: number
  header: string
  type: string
  status: string
  target: string
  limit: string
  reviewer: string
}

const columnHelper = createColumnHelper<typeof features, TableData>()

const columns = columnHelper.columns([
  columnHelper.display({
    id: "drag",
    header: () => null,
    cell: ({ row }) => h(DragHandle),
  }),
  columnHelper.display({
    id: "select",
    header: ({ table }) => h(Checkbox, {
      "modelValue": table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && "indeterminate"),
      "onUpdate:modelValue": value => table.toggleAllPageRowsSelected(!!value),
      "aria-label": t('data_table.select_all'),
    }),
    cell: ({ row }) => h(Checkbox, {
      "modelValue": row.getIsSelected(),
      "onUpdate:modelValue": value => row.toggleSelected(!!value),
      "aria-label": t('data_table.select_row'),
    }),
    enableSorting: false,
    enableHiding: false,
  }),
  columnHelper.accessor("header", {
    header: () => t('data_table.header'),
    cell: ({ row }) => h("div", String(row.getValue("header"))),
    enableHiding: false,
  }),
  columnHelper.accessor("type", {
    header: () => t('data_table.section_type'),
    cell: ({ row }) => h(Badge, {
      variant: "outline",
    }, () => String(row.getValue("type"))),
  }),
  columnHelper.accessor("status", {
    header: () => t('data_table.status'),
    cell: ({ row }) => {
      const status = row.getValue("status") as string
      const isDone = status === "Done"
      return h("div", { class: "flex items-center gap-2" }, [
        isDone
          ? h(IconCircleCheckFilled, { class: "h-4 w-4 text-emerald-500" })
          : h(IconLoader, { class: "h-4 w-4 animate-spin text-muted-foreground" }),
        h("span", {}, isDone ? t('data_table.done') : t('data_table.in_process')),
      ])
    },
  }),
  columnHelper.accessor("target", {
    header: () => h("div", { class: "flex items-center gap-1" }, [
      t('data_table.target'),
    ]),
    cell: ({ row }) => h(Button, {
      variant: "ghost",
      size: "sm",
      class: "h-auto p-1 text-xs font-mono",
    }, () => [
      h("span", { class: "ms-1 font-semibold" }, String(row.getValue("target"))),
    ]),
  }),
  columnHelper.accessor("limit", {
    header: () => h("div", { class: "flex items-center gap-1" }, [
      t('data_table.limit'),
    ]),
    cell: ({ row }) => h(Button, {
      variant: "ghost",
      size: "sm",
      class: "h-auto p-1 text-xs font-mono",
    }, () => [
      h("span", { class: "ms-1 font-semibold" }, String(row.getValue("limit"))),
    ]),
  }),
  columnHelper.accessor("reviewer", {
    header: () => t('data_table.reviewer'),
    cell: ({ row }) => {
      const reviewer = row.getValue("reviewer") as string
      const isAssigned = reviewer !== "Assign reviewer"

      if (isAssigned) {
        return h("span", {}, reviewer)
      }

      return h(Select, {}, {
        default: () => [
          h(SelectTrigger, { class: "w-full" }, {
            default: () => h(SelectValue, { placeholder: t('data_table.assign_reviewer') }),
          }),
          h(SelectContent, {}, {
            default: () => [
              h(SelectItem, { value: "eddie" }, () => "Eddie Lake"),
              h(SelectItem, { value: "jamik" }, () => "Jamik Tashpulatov"),
            ],
          }),
        ],
      })
    },
  }),
  columnHelper.display({
    id: "actions",
    cell: () => h(DropdownMenu, {}, {
      default: () => [
        h(DropdownMenuTrigger, { asChild: true }, {
          default: () => h(Button, {
            variant: "ghost",
            class: "h-8 w-8 p-0",
          }, {
            default: () => [
              h("span", { class: "sr-only" }, t('data_table.open_menu')),
              h(IconDotsVertical, { class: "h-4 w-4" }),
            ],
          }),
        }),
        h(DropdownMenuContent, { align: "end" }, {
          default: () => [
            h(DropdownMenuItem, {}, () => t('common.edit')),
            h(DropdownMenuItem, {}, () => t('data_table.make_a_copy')),
            h(DropdownMenuItem, {}, () => t('data_table.favorite')),
            h(DropdownMenuSeparator, {}),
            h(DropdownMenuItem, {}, () => t('common.delete')),
          ],
        }),
      ],
    }),
  }),
])

// Keep row selection outside the table so the rest of the app can read or update it.
const rowSelection = ref<RowSelectionState>({})

const table = useTable({
  features,
  get data() {
    return props.data
  },
  columns,
  state: {
    get rowSelection() { return rowSelection.value },
  },
  onRowSelectionChange: (updater) => {
    rowSelection.value = typeof updater === "function" ? updater(rowSelection.value) : updater
  },
})
</script>

<template>
  <Tabs
    default-value="outline"
    class="w-full flex-col justify-start gap-6"
  >
    <div class="flex items-center justify-between px-4 lg:px-6">
      <Label for="view-selector" class="sr-only">
        {{ t('data_table.view') }}
      </Label>
      <Select default-value="outline">
        <SelectTrigger
          id="view-selector"
          class="flex w-fit @4xl/main:hidden"
          size="sm"
        >
          <SelectValue :placeholder="t('data_table.select_view')" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="outline">
            {{ t('data_table.outline') }}
          </SelectItem>
          <SelectItem value="past-performance">
            {{ t('data_table.past_performance') }}
          </SelectItem>
          <SelectItem value="key-personnel">
            {{ t('data_table.key_personnel') }}
          </SelectItem>
          <SelectItem value="focus-documents">
            {{ t('data_table.focus_documents') }}
          </SelectItem>
        </SelectContent>
      </Select>
      <TabsList class="**:data-[slot=badge]:bg-muted-foreground/30 hidden **:data-[slot=badge]:size-5 **:data-[slot=badge]:rounded-full **:data-[slot=badge]:px-1 @4xl/main:flex">
        <TabsTrigger value="outline">
          {{ t('data_table.outline') }}
        </TabsTrigger>
        <TabsTrigger value="past-performance">
          {{ t('data_table.past_performance') }} <Badge variant="secondary">
            3
          </Badge>
        </TabsTrigger>
        <TabsTrigger value="key-personnel">
          {{ t('data_table.key_personnel') }} <Badge variant="secondary">
            2
          </Badge>
        </TabsTrigger>
        <TabsTrigger value="focus-documents">
          {{ t('data_table.focus_documents') }}
        </TabsTrigger>
      </TabsList>
      <div class="flex items-center gap-2">
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="outline" size="sm">
              <IconLayoutColumns />
              <span class="hidden lg:inline">{{ t('data_table.customize_columns') }}</span>
              <span class="lg:hidden">{{ t('data_table.columns') }}</span>
              <IconChevronDown />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-56">
            <template v-for="column in table.getAllColumns().filter((column) => typeof column.accessorFn !== 'undefined' && column.getCanHide())" :key="column.id">
              <DropdownMenuCheckboxItem
                class="capitalize"
                :model-value="column.getIsVisible()"
                @update:model-value="(value) => {
                  column.toggleVisibility(!!value)
                }"
              >
                {{ column.id }}
              </DropdownMenuCheckboxItem>
            </template>
          </DropdownMenuContent>
        </DropdownMenu>
        <Button variant="outline" size="sm">
          <IconPlus />
          <span class="hidden lg:inline">{{ t('data_table.add_section') }}</span>
        </Button>
      </div>
    </div>
    <TabsContent
      value="outline"
      class="relative flex flex-col gap-4 overflow-auto px-4 lg:px-6"
    >
      <div class="overflow-hidden rounded-lg border">
        <DragDropProvider :modifiers="[RestrictToVerticalAxis]">
          <Table>
            <TableHeader class="bg-muted sticky top-0 z-10">
              <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                <TableHead v-for="header in headerGroup.headers" :key="header.id" :colspan="header.colSpan">
                  <FlexRender v-if="!header.isPlaceholder" :header="header" />
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody class="**:data-[slot=table-cell]:first:w-8">
              <template v-if="table.getRowModel().rows.length">
                <DraggableRow v-for="row in table.getRowModel().rows" :key="row.id" :row="row" :index="row.index" />
              </template>
              <TableRow v-else>
                <TableCell
                  :colspan="columns.length"
                  class="h-24 text-center"
                >
                  {{ t('data_table.no_results') }}
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </DragDropProvider>
      </div>
      <div class="flex items-center justify-between px-4">
        <div class="text-muted-foreground hidden flex-1 text-sm lg:flex">
          {{ t('data_table.rows_selected', { count: table.getFilteredSelectedRowModel().rows.length, total: table.getFilteredRowModel().rows.length }) }}
        </div>
        <div class="flex w-full items-center gap-8 lg:w-fit">
          <div class="hidden items-center gap-2 lg:flex">
            <Label for="rows-per-page" class="text-sm font-medium">
              {{ t('data_table.rows_per_page') }}
            </Label>
            <Select
              :model-value="table.atoms.pagination.get().pageSize"
              @update:model-value="(value) => {
                table.setPageSize(Number(value))
              }"
            >
              <SelectTrigger id="rows-per-page" size="sm" class="w-20">
                <SelectValue :placeholder="`${table.atoms.pagination.get().pageSize}`" />
              </SelectTrigger>
              <SelectContent side="top">
                <SelectItem v-for="pageSize in [10, 20, 30, 40, 50]" :key="pageSize" :value="`${pageSize}`">
                  {{ pageSize }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex w-fit items-center justify-center text-sm font-medium">
            {{ t('data_table.page_x_of_y', { current: table.atoms.pagination.get().pageIndex + 1, total: table.getPageCount() }) }}
          </div>
          <div class="ms-auto flex items-center gap-2 lg:ms-0">
            <Button
              variant="outline"
              class="hidden h-8 w-8 p-0 lg:flex"
              :disabled="!table.getCanPreviousPage()"
              @click="table.setPageIndex(0)"
            >
              <span class="sr-only">{{ t('data_table.first_page') }}</span>
              <IconChevronsLeft class="rtl:rotate-180" />
            </Button>
            <Button
              variant="outline"
              class="size-8"
              size="icon"
              :disabled="!table.getCanPreviousPage()"
              @click="table.previousPage()"
            >
              <span class="sr-only">{{ t('data_table.prev_page') }}</span>
              <IconChevronLeft class="rtl:rotate-180" />
            </Button>
            <Button
              variant="outline"
              class="size-8"
              size="icon"
              :disabled="!table.getCanNextPage()"
              @click="table.nextPage()"
            >
              <span class="sr-only">{{ t('data_table.next_page') }}</span>
              <IconChevronRight class="rtl:rotate-180" />
            </Button>
            <Button
              variant="outline"
              class="hidden size-8 lg:flex"
              size="icon"
              :disabled="!table.getCanNextPage()"
              @click="table.setPageIndex(table.getPageCount() - 1)"
            >
              <span class="sr-only">{{ t('data_table.last_page') }}</span>
              <IconChevronsRight class="rtl:rotate-180" />
            </Button>
          </div>
        </div>
      </div>
    </TabsContent>
    <TabsContent
      value="past-performance"
      class="flex flex-col px-4 lg:px-6"
    >
      <div class="aspect-video w-full flex-1 rounded-lg border border-dashed" />
    </TabsContent>
    <TabsContent value="key-personnel" class="flex flex-col px-4 lg:px-6">
      <div class="aspect-video w-full flex-1 rounded-lg border border-dashed" />
    </TabsContent>
    <TabsContent
      value="focus-documents"
      class="flex flex-col px-4 lg:px-6"
    >
      <div class="aspect-video w-full flex-1 rounded-lg border border-dashed" />
    </TabsContent>
  </Tabs>
</template>
