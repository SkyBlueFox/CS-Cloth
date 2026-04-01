<script lang="ts">
	import './layout.css';
	import favicon from '$lib/assets/favicon.svg';
	import SidebarIcon from '$lib/components/SidebarIcon.svelte';
	import type { IconName } from '$lib/components/SidebarIcon.svelte';
	import { page } from '$app/stores';
	import type { LayoutData } from './$types';
	import type { Snippet } from 'svelte';

	let { children, data } = $props<{ children: Snippet; data: LayoutData }>();
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

	const visibleNav = $derived(nav.filter((item) => data.user && item.roles.includes(data.user.role)));
	const homeHref = $derived(
		data.user?.role === 'superadmin'
			? '/superadmin/reports'
			: data.user?.role === 'admin'
			? '/admin/items'
			: '/shop'
	);
</script>

<svelte:head>
	<link rel="icon" href={favicon} />
</svelte:head>

<div class="min-h-screen text-slate-900">
	<div class="flex min-h-screen">
		{#if data.user}
			<aside
				aria-hidden={!sidebarOpen}
				class={`sidebar-shell ${sidebarOpen ? 'w-72 opacity-100' : 'w-0 border-r-0 opacity-0'}`}
			>
				<div class="flex h-full flex-col">
					<div
						class={`border-b border-sky-100/80 px-5 py-5 ${
							sidebarOpen ? 'flex items-center justify-between gap-3' : 'flex justify-center'
						}`}
					>
						<a
							class={`font-semibold tracking-[0.18em] text-blue-800 ${
								sidebarOpen ? 'text-lg' : 'text-sm'
							}`}
							href="/"
						>
							{sidebarOpen ? 'CS CLOTH' : 'CSC'}
						</a>
					</div>

					<nav class="flex-1 space-y-2 px-4 py-5">
						{#each visibleNav as item (item.href)}
							{@const isActive = $page.url.pathname.startsWith(item.href)}
							<a
								aria-current={isActive ? 'page' : undefined}
								class={`sidebar-link ${isActive ? 'sidebar-link-active' : ''}`}
								href={item.href}
								title={item.label}
							>
								<SidebarIcon active={isActive} name={item.icon} />
								<span>{item.label}</span>
							</a>
						{/each}
					</nav>
				</div>
			</aside>
		{/if}

		<div class="flex min-w-0 flex-1 flex-col">
			<header class="border-b border-sky-100/80 bg-white/80 backdrop-blur">
				<div class="flex items-center justify-between gap-6 px-6 py-4">
					<div class="flex items-center gap-3">
						{#if data.user}
							<button
								aria-label={sidebarOpen ? 'Collapse sidebar' : 'Expand sidebar'}
								class="btn-secondary flex h-11 w-11 items-center justify-center rounded-2xl p-0 text-lg"
								type="button"
								onclick={() => (sidebarOpen = !sidebarOpen)}
							>
								☰
							</button>
						{/if}

						<div>
							<p class="eyebrow">CS Cloth</p>
							<a class="block text-lg font-semibold text-sky-950 hover:text-blue-800" href={homeHref}>
								{#if data.user} Workspace {:else} Storefront {/if}
							</a>
						</div>
					</div>

					<div class="flex items-center gap-3">
						{#if data.user}
							<div class="text-right text-sm">
								<p class="font-medium">{data.user.name}</p>
								<p class="text-sky-700">
									{data.user.role.toUpperCase()}
									{#if data.user.role === 'user'} · ฿{data.user.balance.toFixed(2)} {/if}
								</p>
							</div>

							<form method="POST" action="/logout">
								<button class="btn-secondary rounded-full px-4 py-2 text-sm" type="submit">Logout</button>
							</form>
						{:else}
							<a class="btn-secondary rounded-full px-4 py-2 text-sm" href="/login">Login</a>
							<a class="btn-primary rounded-full px-4 py-2 text-sm" href="/register">Register</a>
						{/if}
					</div>
				</div>
			</header>

			<main class="flex-1 px-6 py-10">
				<div class="mx-auto max-w-7xl">
					{@render children()}
				</div>
			</main>
		</div>
	</div>
</div>
