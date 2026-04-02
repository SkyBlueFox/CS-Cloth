<script lang="ts">
	import type { User } from '$lib/types';

	let { data, form } = $props();
	let editingAdminId = $state<number | null>(null);
	let search = $state('');
	let sort = $state<'az' | 'za' | 'newest' | 'oldest'>('az');

	const matchesSearch = (admin: User, term: string) => {
		const query = term.trim().toLowerCase();
		if (!query) {
			return true;
		}

		return [admin.name, admin.email].some((value) => value.toLowerCase().includes(query));
	};

	const compareAdmins = (left: User, right: User) => {
		if (sort === 'az') {
			return left.name.localeCompare(right.name);
		}

		if (sort === 'za') {
			return right.name.localeCompare(left.name);
		}

		const leftTime = Date.parse(left.deleted_at ?? left.created_at ?? '') || 0;
		const rightTime = Date.parse(right.deleted_at ?? right.created_at ?? '') || 0;

		return sort === 'oldest' ? leftTime - rightTime : rightTime - leftTime;
	};

	const visibleAdmins = (admins: User[]) => admins.filter((admin) => matchesSearch(admin, search)).sort(compareAdmins);
</script>

<section class="space-y-8">
	<div class="panel space-y-5">
		<div>
			<p class="eyebrow">Superadmin</p>
			<h1 class="mt-2 text-3xl font-semibold">Manage admins</h1>
			<p class="mt-2 max-w-2xl text-sm text-slate-600">Active admins stay editable here. Deactivated admins are kept in a separate archive so you can restore access without losing their record.</p>
		</div>
		<div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_16rem]">
			<input bind:value={search} class="rounded-2xl border-slate-300" placeholder="Search by admin name or email" />
			<select bind:value={sort} class="rounded-2xl border-slate-300">
				<option value="az">Name A-Z</option>
				<option value="za">Name Z-A</option>
				<option value="newest">Newest first</option>
				<option value="oldest">Oldest first</option>
			</select>
		</div>
	</div>
	{#if form?.error}
		<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
	{/if}
	{#if form?.success}
		<p class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{form.success}</p>
	{/if}
	<form class="panel grid gap-4 md:grid-cols-2" method="POST" action="?/create">
		<input class="rounded-2xl border-slate-300" name="name" placeholder="Admin name" required />
		<input class="rounded-2xl border-slate-300" name="email" placeholder="Admin email" type="email" required />
		<input class="rounded-2xl border-slate-300" name="password" placeholder="Password" type="password" required />
		<input class="rounded-2xl border-slate-300" name="password_confirmation" placeholder="Confirm password" type="password" required />
		<button class="btn-primary md:col-span-2" type="submit">Create admin</button>
	</form>
	<div class="space-y-4">
		<div class="flex items-center justify-between">
			<div>
				<h2 class="text-xl font-semibold">Active admins</h2>
				<p class="mt-1 text-sm text-slate-600">{visibleAdmins(data.active).length} shown</p>
			</div>
		</div>
		{#if visibleAdmins(data.active).length}
			{#each visibleAdmins(data.active) as admin}
				{#if editingAdminId === admin.id}
					<form class="panel grid gap-3 lg:grid-cols-[1fr_1fr_0.9fr_0.9fr_auto_auto]" method="POST" action="?/update">
						<input name="user_id" type="hidden" value={admin.id} />
						<input class="rounded-2xl border-slate-300" name="name" value={admin.name} required />
						<input class="rounded-2xl border-slate-300" name="email" type="email" value={admin.email} required />
						<input class="rounded-2xl border-slate-300" name="password" placeholder="New password" type="password" />
						<input class="rounded-2xl border-slate-300" name="password_confirmation" placeholder="Confirm" type="password" />
						<button class="btn-secondary rounded-full px-4 py-2 text-sm" type="submit">Save</button>
						<button class="btn-secondary rounded-full px-4 py-2 text-sm" onclick={() => (editingAdminId = null)} type="button">Cancel</button>
					</form>
				{:else}
					<div class="panel flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
						<div>
							<h3 class="text-xl font-semibold">{admin.name}</h3>
							<p class="mt-1 text-sm text-slate-600">{admin.email}</p>
						</div>
						<div class="flex gap-3">
							<button class="btn-secondary" onclick={() => (editingAdminId = admin.id)} type="button">Edit</button>
							<form method="POST" action="?/delete">
								<input name="user_id" type="hidden" value={admin.id} />
								<button class="btn-danger" type="submit">Deactivate</button>
							</form>
						</div>
					</div>
				{/if}
			{/each}
		{:else}
			<div class="panel text-sm text-slate-600">No active admins match the current search.</div>
		{/if}
	</div>
	<div class="space-y-4">
		<div class="flex items-center justify-between">
			<div>
				<h2 class="text-xl font-semibold">Deactivated admins</h2>
				<p class="mt-1 text-sm text-slate-600">{visibleAdmins(data.deactivated).length} shown</p>
			</div>
		</div>
		{#if visibleAdmins(data.deactivated).length}
			{#each visibleAdmins(data.deactivated) as admin}
				<div class="panel flex flex-col gap-4 border-slate-200 bg-slate-50/80 lg:flex-row lg:items-center lg:justify-between">
					<div>
						<h3 class="text-xl font-semibold text-slate-800">{admin.name}</h3>
						<p class="mt-1 text-sm text-slate-600">{admin.email}</p>
						{#if admin.deleted_at}
							<p class="mt-2 text-xs uppercase tracking-[0.24em] text-slate-500">Deactivated {new Date(admin.deleted_at).toLocaleString()}</p>
						{/if}
					</div>
					<form method="POST" action="?/restore">
						<input name="user_id" type="hidden" value={admin.id} />
						<button class="btn-secondary" type="submit">Reactivate</button>
					</form>
				</div>
			{/each}
		{:else}
			<div class="panel text-sm text-slate-600">No deactivated admins match the current search.</div>
		{/if}
	</div>
</section>
