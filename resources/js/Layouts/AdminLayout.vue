<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';
import {
    LayoutDashboard, Radio, Film, Tv, Users, Server, Settings,
    ChevronLeft, ChevronRight, Moon, Sun, LogOut, Menu, X,
    MonitorPlay, Package, FolderTree, ListVideo, Shield,
    Activity, Key, Wifi, Sliders, Smartphone, Ticket, BookOpen,
    ShieldBan, ShieldAlert, Globe,
} from 'lucide-vue-next';

const page = usePage();
const collapsed = ref(false);
const mobileOpen = ref(false);
const dark = ref(true);

function toggleDark() {
    dark.value = !dark.value;
    document.documentElement.classList.toggle('dark', dark.value);
}

interface NavItem {
    label: string;
    href: string;
    icon: any;
    active?: boolean;
    children?: NavItem[];
}

interface NavGroup {
    title: string;
    items: NavItem[];
}

const navGroups = computed<NavGroup[]>(() => {
    const path = page.url;
    return [
        {
            title: 'Main',
            items: [
                { label: 'Dashboard', href: '/admin', icon: LayoutDashboard, active: path === '/admin' },
                { label: 'Connections', href: '/admin/connections', icon: Wifi, active: path.startsWith('/admin/connections') },
            ],
        },
        {
            title: 'Content',
            items: [
                { label: 'Streams', href: '/admin/streams', icon: Radio, active: path.startsWith('/admin/streams') },
                { label: 'Movies', href: '/admin/movies', icon: Film, active: path.startsWith('/admin/movies') },
                { label: 'Series', href: '/admin/series', icon: Tv, active: path.startsWith('/admin/series') },
                { label: 'Categories', href: '/admin/categories', icon: FolderTree, active: path.startsWith('/admin/categories') },
                { label: 'Bouquets', href: '/admin/bouquets', icon: ListVideo, active: path.startsWith('/admin/bouquets') },
                { label: 'EPG', href: '/admin/epgs', icon: BookOpen, active: path.startsWith('/admin/epgs') },
            ],
        },
        {
            title: 'Users',
            items: [
                { label: 'Lines', href: '/admin/lines', icon: MonitorPlay, active: path.startsWith('/admin/lines') },
                { label: 'Packages', href: '/admin/packages', icon: Package, active: path.startsWith('/admin/packages') },
                { label: 'Users', href: '/admin/users', icon: Users, active: path.startsWith('/admin/users') },
                { label: 'Groups', href: '/admin/groups', icon: Shield, active: path.startsWith('/admin/groups') },
                { label: 'Devices', href: '/admin/devices', icon: Smartphone, active: path.startsWith('/admin/devices') },
                { label: 'Tickets', href: '/admin/tickets', icon: Ticket, active: path.startsWith('/admin/tickets') },
            ],
        },
        {
            title: 'Security',
            items: [
                { label: 'Blocked IPs', href: '/admin/blocked-ips', icon: ShieldBan, active: path.startsWith('/admin/blocked-ips') },
                { label: 'Blocked ISPs', href: '/admin/blocked-isps', icon: Globe, active: path.startsWith('/admin/blocked-isps') },
                { label: 'Blocked UAs', href: '/admin/blocked-uas', icon: ShieldAlert, active: path.startsWith('/admin/blocked-uas') },
                { label: 'Access Codes', href: '/admin/access-codes', icon: Key, active: path.startsWith('/admin/access-codes') },
                { label: 'Activity Logs', href: '/admin/activity-logs', icon: Activity, active: path.startsWith('/admin/activity-logs') },
            ],
        },
        {
            title: 'System',
            items: [
                { label: 'Servers', href: '/admin/servers', icon: Server, active: path.startsWith('/admin/servers') },
                { label: 'Profiles', href: '/admin/transcode-profiles', icon: Sliders, active: path.startsWith('/admin/transcode-profiles') },
                { label: 'Settings', href: '/admin/settings', icon: Settings, active: path.startsWith('/admin/settings') },
            ],
        },
    ];
});

const user = computed(() => (page.props.auth as any)?.user);

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-background">
        <!-- Mobile overlay -->
        <Transition name="fade">
            <div v-if="mobileOpen" class="fixed inset-0 z-40 bg-black/60 lg:hidden" @click="mobileOpen = false" />
        </Transition>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex flex-col border-r bg-sidebar text-sidebar-foreground transition-all duration-300 lg:static',
                collapsed ? 'w-16' : 'w-64',
                mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            ]"
        >
            <!-- Logo -->
            <div class="flex h-16 items-center border-b border-sidebar-border px-4">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary text-primary-foreground font-bold text-sm">
                        XC
                    </div>
                    <Transition name="fade-text">
                        <span v-if="!collapsed" class="text-lg font-semibold whitespace-nowrap">XC_VM</span>
                    </Transition>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-2 py-4 space-y-4">
                <div v-for="group in navGroups" :key="group.title">
                    <p v-if="!collapsed" class="px-3 mb-1 text-[10px] font-semibold uppercase tracking-wider text-sidebar-foreground/40">
                        {{ group.title }}
                    </p>
                    <div v-else class="mb-1 border-b border-sidebar-border mx-2" />
                    <Link
                        v-for="item in group.items"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                            item.active
                                ? 'bg-sidebar-accent text-sidebar-accent-foreground'
                                : 'text-sidebar-foreground/70 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
                            collapsed ? 'justify-center' : '',
                        ]"
                        @click="mobileOpen = false"
                    >
                        <component :is="item.icon" class="h-4.5 w-4.5 shrink-0" />
                        <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
                    </Link>
                </div>
            </nav>

            <!-- Collapse toggle -->
            <div class="hidden border-t border-sidebar-border p-2 lg:block">
                <button
                    class="flex w-full items-center justify-center rounded-lg p-2 text-sidebar-foreground/70 hover:bg-sidebar-accent transition-colors cursor-pointer"
                    @click="collapsed = !collapsed"
                >
                    <ChevronLeft v-if="!collapsed" class="h-5 w-5" />
                    <ChevronRight v-else class="h-5 w-5" />
                </button>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="flex h-16 items-center justify-between border-b bg-card px-4 lg:px-6">
                <div class="flex items-center gap-4">
                    <button class="lg:hidden cursor-pointer" @click="mobileOpen = !mobileOpen">
                        <Menu v-if="!mobileOpen" class="h-6 w-6" />
                        <X v-else class="h-6 w-6" />
                    </button>
                    <div>
                        <h1 class="text-lg font-semibold">
                            <slot name="title">Dashboard</slot>
                        </h1>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        class="rounded-lg p-2 hover:bg-accent transition-colors cursor-pointer"
                        @click="toggleDark"
                    >
                        <Moon v-if="dark" class="h-5 w-5" />
                        <Sun v-else class="h-5 w-5" />
                    </button>
                    <div class="hidden sm:flex items-center gap-2 text-sm">
                        <div class="h-8 w-8 rounded-full bg-primary/20 flex items-center justify-center text-primary font-semibold text-xs">
                            {{ user?.username?.charAt(0).toUpperCase() || 'A' }}
                        </div>
                        <span class="font-medium">{{ user?.username || 'Admin' }}</span>
                    </div>
                    <button
                        class="rounded-lg p-2 hover:bg-accent transition-colors text-muted-foreground hover:text-foreground cursor-pointer"
                        @click="logout"
                    >
                        <LogOut class="h-5 w-5" />
                    </button>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                <slot />
            </main>
        </div>

        <Toast />
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.fade-text-enter-active, .fade-text-leave-active { transition: opacity 0.15s ease; }
.fade-text-enter-from, .fade-text-leave-to { opacity: 0; }
</style>
