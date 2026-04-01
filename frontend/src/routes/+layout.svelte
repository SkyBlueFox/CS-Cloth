<script lang="ts">
    import './layout.css';
    import favicon from '$lib/assets/favicon.svg';
    import SidebarIcon from '$lib/components/SidebarIcon.svelte';
    import type { IconName } from '$lib/components/SidebarIcon.svelte';
    import { page } from '$app/stores';
    import { user } from '$lib/stores/auth';
    import type { LayoutData } from './$types';
    import type { Snippet } from 'svelte';

    let { children, data } = $props<{ children: Snippet; data: LayoutData }>();
    
    // ดึงข้อมูล User จาก store หรือ page data
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

<div class="flex min-h-screen flex-col bg-slate-50 font-sans text-slate-900 lg:flex-row">
    
    {#if currentUser && !isPublicPage}
        <aside
            aria-hidden={!sidebarOpen}
            class={`fixed inset-y-0 left-0 z-50 flex flex-col border-r border-slate-200 bg-white transition-all duration-300 lg:static ${
                sidebarOpen ? 'w-64 translate-x-0' : 'w-0 -translate-x-full overflow-hidden border-none'
            }`}
        >
            <div class="flex h-16 shrink-0 items-center border-b border-slate-100 px-6">
                <a class="text-base font-bold tracking-[0.2em] text-blue-800" href="/">
                    {sidebarOpen ? 'CS CLOTH' : ''}
                </a>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto p-4">
                {#each visibleNav as item (item.href)}
                    {@const isActive = $page.url.pathname.startsWith(item.href)}
                    <a
                        aria-current={isActive ? 'page' : undefined}
                        class={`flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-colors ${
                            isActive 
                                ? 'bg-blue-50 text-blue-700' 
                                : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                        }`}
                        href={item.href}
                        title={item.label}
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
            <header class="sticky top-0 z-40 flex h-16 shrink-0 items-center justify-between border-b border-slate-200 bg-white px-6 sm:px-8">
                
                <div class="flex items-center gap-4 sm:gap-6">
                    {#if currentUser}
                        <button
                            aria-label="Toggle sidebar"
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-50 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900"
                            type="button"
                            onclick={() => (sidebarOpen = !sidebarOpen)}
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    {/if}

                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">CS Cloth</span>
                        <span class="text-sm font-bold text-slate-900">Workspace</span>
                    </div>
                </div>

                <div class="flex items-center gap-4 sm:gap-6">
                    {#if currentUser}
                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-semibold text-slate-800">{currentUser.name}</p>
                            <div class="mt-0.5 inline-block rounded bg-blue-50 px-2 py-0.5 text-[10px] font-bold tracking-wider text-blue-600">
                                {currentUser.role.toUpperCase()}
                                {#if currentUser.role === 'user'} · ฿{currentUser.balance.toFixed(2)}{/if}
                            </div>
                        </div>

                        <div class="hidden h-8 w-px bg-slate-200 sm:block"></div>

                        <form method="POST" action="/logout">
                            <button class="text-sm font-medium text-slate-500 transition-colors hover:text-slate-900" type="submit">
                                Logout
                            </button>
                        </form>
                    {:else}
                        <a class="text-sm font-medium text-slate-500 hover:text-slate-900" href="/login">Login</a>
                        <a class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700" href="/register">Register</a>
                    {/if}
                </div>
            </header>
        {/if}

        <main class="flex-1 overflow-auto p-6 sm:p-8">
            <div class="mx-auto max-w-7xl">
                {#if currentUser || isPublicPage}
                    {@render children()}
                {:else}
                    <div class="flex h-[60vh] flex-col items-center justify-center text-center">
                        <h1 class="text-2xl font-bold text-slate-900">Access Denied</h1>
                        <p class="mt-2 text-slate-500">Please sign in to view this page.</p>
                        <a href="/login" class="mt-6 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-700">
                            Go to Login
                        </a>
                    </div>
                {/if}
            </div>
        </main>
        
    </div>
</div>