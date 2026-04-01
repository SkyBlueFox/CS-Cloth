<script lang="ts">
    import './layout.css';
    import favicon from '$lib/assets/favicon.svg';
    import SidebarIcon from '$lib/components/SidebarIcon.svelte';
    import type { IconName } from '$lib/components/SidebarIcon.svelte';
    import { page } from '$app/stores';
    import { user } from '$lib/stores/auth';
    import type { LayoutData } from './$types';
    import type { Snippet } from 'svelte';
    import { fade } from 'svelte/transition';

    let { children, data } = $props<{ children: Snippet; data: LayoutData }>();
    
    const currentUser = $derived($user || data.user);
    let sidebarOpen = $state(true);

    type NavItem = {
        href: string;
        label: string;
        roles: string[];
        icon: IconName;
    };

    const nav: NavItem[] = [
        { href: '/shop', label: 'Shop', roles: ['user'], icon: 'shop' as IconName },
        { href: '/orders', label: 'My Orders', roles: ['user'], icon: 'orders' as IconName },
        { href: '/wallet', label: 'Wallet', roles: ['user'], icon: 'wallet' as IconName },
        { href: '/questions', label: 'My Questions', roles: ['user'], icon: 'questions' as IconName },
        { href: '/admin/orders', label: 'Manage Orders', roles: ['admin'], icon: 'orders' as IconName },
        { href: '/admin/items', label: 'Manage Items', roles: ['admin'], icon: 'items' as IconName },
        { href: '/admin/questions', label: 'Pending Questions', roles: ['admin'], icon: 'questions' as IconName },
        { href: '/superadmin/admins', label: 'Manage Admins', roles: ['superadmin'], icon: 'admins' as IconName },
        { href: '/superadmin/users', label: 'Manage Users', roles: ['superadmin'], icon: 'users' as IconName },
        { href: '/superadmin/reports', label: 'Manage Reports', roles: ['superadmin'], icon: 'reports' as IconName },
        { href: '/profile', label: 'Profile', roles: ['user', 'admin', 'superadmin'], icon: 'profile' as IconName }
    ];

    const visibleNav = $derived(nav.filter((item) => currentUser && item.roles.includes(currentUser.role)));
    const isPublicPage = $derived(['/login', '/register'].includes($page.url.pathname));
</script>

<svelte:head>
    <link rel="icon" href={favicon} />
</svelte:head>

<div class="flex min-h-screen flex-col lg:flex-row">
    
    {#if currentUser && !isPublicPage}
        <aside
            class="fixed inset-y-0 left-0 z-50 flex flex-col border-r border-slate-200 bg-white transition-all duration-500 lg:static {sidebarOpen ? 'w-72' : 'w-0 -translate-x-full'}"
        >
            <div class="flex h-20 shrink-0 items-center border-b border-slate-100 px-8">
                <a class="text-xl font-black tracking-[0.3em] text-blue-700" href="/">
                    {sidebarOpen ? 'CS CLOTH' : ''}
                </a>
            </div>

            <nav class="flex-1 space-y-2 overflow-y-auto p-6">
                {#each visibleNav as item (item.href)}
                    {@const isActive = $page.url.pathname.startsWith(item.href)}
                    <a
                        class="flex items-center gap-4 rounded-2xl px-4 py-3.5 text-sm font-bold transition-all {isActive 
                            ? 'bg-slate-900 text-white shadow-xl shadow-slate-900/20' 
                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'}"
                        href={item.href}
                    >
                        <SidebarIcon active={isActive} name={item.icon} />
                        <span>{item.label}</span>
                    </a>
                {/each}
            </nav>
        </aside>
    {/if}

    <div class="flex min-w-0 flex-1 flex-col">
        
        {#if !isPublicPage}
            <header class="sticky top-0 z-40 flex h-20 shrink-0 items-center justify-between border-b border-slate-100 bg-white/80 px-8 backdrop-blur-md">
                <div class="flex items-center gap-6">
                    {#if currentUser}
                        <button
                            onclick={() => (sidebarOpen = !sidebarOpen)}
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    {/if}

                    <div class="flex flex-col">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Workspace</span>
                        <span class="text-sm font-black text-slate-900 uppercase tracking-tight">Store Management</span>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    {#if currentUser}
                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-black text-slate-900">{currentUser.name}</p>
                            <div class="mt-0.5 inline-flex items-center rounded-full bg-blue-50 px-3 py-0.5 text-[10px] font-black tracking-wider text-blue-700 ring-1 ring-blue-100">
                                {currentUser.role.toUpperCase()}
                                {#if currentUser.role === 'user'} · ฿{currentUser.balance?.toLocaleString()}{/if}
                            </div>
                        </div>

                        <div class="hidden h-8 w-px bg-slate-200 sm:block"></div>

                        <form method="POST" action="/logout">
                            <button class="text-xs font-black uppercase tracking-widest text-slate-400 transition-colors hover:text-rose-600">
                                Logout
                            </button>
                        </form>
                    {:else}
                        <a class="text-sm font-bold text-slate-600 hover:text-slate-900" href="/login">Login</a>
                        <a class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-600/20 hover:bg-blue-700" href="/register">Register</a>
                    {/if}
                </div>
            </header>
        {/if}

        <main class="flex-1 p-8 sm:p-12">
            <div class="mx-auto max-w-7xl">
                {#if currentUser || isPublicPage}
                    {@render children()}
                {:else}
                    <div class="flex h-[60vh] flex-col items-center justify-center text-center" in:fade>
                        <div class="mb-6 rounded-3xl bg-slate-100 p-6">
                            <svg class="h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </div>
                        <h1 class="text-3xl font-black text-slate-900">Access Denied</h1>
                        <p class="mt-2 font-bold text-slate-500 uppercase tracking-widest text-xs">Authentication Required</p>
                        <a href="/login" class="btn-primary mt-8">Go to Login</a>
                    </div>
                {/if}
            </div>
        </main>
    </div>
</div>