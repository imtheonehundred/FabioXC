<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Select from '@/Components/ui/Select.vue';
import { Search, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, ArrowUpDown, ArrowUp, ArrowDown, Trash2 } from 'lucide-vue-next';

export interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    class?: string;
}

interface Props {
    columns: Column[];
    rows: any[];
    total?: number;
    currentPage?: number;
    lastPage?: number;
    perPage?: number;
    from?: number | null;
    to?: number | null;
    routeName: string;
    searchable?: boolean;
    selectable?: boolean;
    filters?: Record<string, any>;
}

const props = withDefaults(defineProps<Props>(), {
    total: 0,
    currentPage: 1,
    lastPage: 1,
    perPage: 25,
    searchable: true,
    selectable: true,
    filters: () => ({}),
});

const emit = defineEmits<{
    'bulk-action': [action: string, ids: number[]];
}>();

const search = ref(props.filters?.search || '');
const sortField = ref(props.filters?.sort || '');
const sortDirection = ref(props.filters?.direction || 'asc');
const selected = ref<number[]>([]);
const perPage = ref(String(props.perPage));

const allSelected = computed(() =>
    props.rows.length > 0 && selected.value.length === props.rows.length,
);

function toggleAll() {
    if (allSelected.value) {
        selected.value = [];
    } else {
        selected.value = props.rows.map((r) => r.id);
    }
}

function toggleRow(id: number) {
    const idx = selected.value.indexOf(id);
    if (idx > -1) selected.value.splice(idx, 1);
    else selected.value.push(id);
}

function applyFilters(extra: Record<string, any> = {}) {
    const params: Record<string, any> = {
        ...props.filters,
        search: search.value || undefined,
        sort: sortField.value || undefined,
        direction: sortDirection.value || undefined,
        per_page: perPage.value,
        ...extra,
    };
    Object.keys(params).forEach((k) => {
        if (params[k] === undefined || params[k] === '') delete params[k];
    });
    router.get(route(props.routeName), params, { preserveState: true, preserveScroll: true });
}

function sort(key: string) {
    if (sortField.value === key) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = key;
        sortDirection.value = 'asc';
    }
    applyFilters({ page: 1 });
}

function goToPage(page: number) {
    applyFilters({ page });
}

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters({ page: 1 }), 400);
});

watch(perPage, () => applyFilters({ page: 1 }));

function bulkAction(action: string) {
    if (selected.value.length === 0) return;
    emit('bulk-action', action, [...selected.value]);
    selected.value = [];
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                <div v-if="searchable" class="relative">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="Search..."
                        class="pl-8 w-64"
                    />
                </div>
                <slot name="filters" />
            </div>
            <div class="flex items-center gap-2">
                <slot name="actions" :selected="selected" :bulk-action="bulkAction" />
                <div v-if="selectable && selected.length > 0" class="text-sm text-muted-foreground">
                    {{ selected.length }} selected
                </div>
            </div>
        </div>

        <div class="rounded-md border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th v-if="selectable" class="w-12 px-4 py-3">
                            <input
                                type="checkbox"
                                :checked="allSelected"
                                class="rounded border-input"
                                @change="toggleAll"
                            />
                        </th>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            :class="[
                                'px-4 py-3 text-left font-medium text-muted-foreground',
                                col.sortable ? 'cursor-pointer select-none hover:text-foreground' : '',
                                col.class,
                            ]"
                            @click="col.sortable && sort(col.key)"
                        >
                            <div class="flex items-center gap-1">
                                {{ col.label }}
                                <template v-if="col.sortable">
                                    <ArrowUp v-if="sortField === col.key && sortDirection === 'asc'" class="h-3.5 w-3.5" />
                                    <ArrowDown v-else-if="sortField === col.key && sortDirection === 'desc'" class="h-3.5 w-3.5" />
                                    <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                </template>
                            </div>
                        </th>
                        <th class="w-20 px-4 py-3 text-right font-medium text-muted-foreground">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="rows.length === 0">
                        <td :colspan="columns.length + (selectable ? 2 : 1)" class="px-4 py-12 text-center text-muted-foreground">
                            No results found.
                        </td>
                    </tr>
                    <tr
                        v-for="row in rows"
                        :key="row.id"
                        class="border-b transition-colors hover:bg-muted/50"
                        :class="{ 'bg-muted/30': selected.includes(row.id) }"
                    >
                        <td v-if="selectable" class="px-4 py-3">
                            <input
                                type="checkbox"
                                :checked="selected.includes(row.id)"
                                class="rounded border-input"
                                @change="toggleRow(row.id)"
                            />
                        </td>
                        <td v-for="col in columns" :key="col.key" :class="['px-4 py-3', col.class]">
                            <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                                {{ row[col.key] }}
                            </slot>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <slot name="row-actions" :row="row" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                <span v-if="from && to">Showing {{ from }} to {{ to }} of {{ total }} results</span>
                <span v-else>{{ total }} results</span>
                <Select v-model="perPage" class="w-20 h-8 text-xs">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </Select>
            </div>
            <div v-if="lastPage > 1" class="flex items-center gap-1">
                <Button variant="outline" size="icon" class="h-8 w-8" :disabled="currentPage === 1" @click="goToPage(1)">
                    <ChevronsLeft class="h-4 w-4" />
                </Button>
                <Button variant="outline" size="icon" class="h-8 w-8" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                <span class="px-3 text-sm">Page {{ currentPage }} of {{ lastPage }}</span>
                <Button variant="outline" size="icon" class="h-8 w-8" :disabled="currentPage === lastPage" @click="goToPage(currentPage + 1)">
                    <ChevronRight class="h-4 w-4" />
                </Button>
                <Button variant="outline" size="icon" class="h-8 w-8" :disabled="currentPage === lastPage" @click="goToPage(lastPage)">
                    <ChevronsRight class="h-4 w-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
