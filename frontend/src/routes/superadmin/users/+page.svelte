<script lang="ts">
	import type { User } from '$lib/types';

	let { data, form } = $props();
	let editingUserId = $state<number | null>(null);
	let search = $state('');
	let sort = $state<'az' | 'za' | 'newest' | 'oldest'>('az');

	const matchesSearch = (user: User, term: string) => {
		const query = term.trim().toLowerCase();
		if (!query) {
			return true;
		}

		return [user.name, user.email].some((value) => value.toLowerCase().includes(query));
	};

	const compareUsers = (left: User, right: User) => {
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

	const visibleUsers = (users: User[]) => users.filter((user) => matchesSearch(user, search)).sort(compareUsers);
</script>

<section class="space-y-8">
	<div class="panel space-y-5">
		<div>
			<p class="eyebrow">Superadmin</p>
			<h1 class="mt-2 text-3xl font-semibold">Manage users</h1>
			<p class="mt-2 max-w-2xl text-sm text-slate-600">Keep the active customer list editable while retaining a clear archive of deactivated accounts that can be restored at any time.</p>
		</div>
		<div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_16rem]">
			<input bind:value={search} class="rounded-2xl border-slate-300" placeholder="Search by user name or email" />
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
		<input class="rounded-2xl border-slate-300" name="name" placeholder="User name" required />
		<input class="rounded-2xl border-slate-300" name="email" placeholder="User email" type="email" required />
		<input class="rounded-2xl border-slate-300" name="password" placeholder="Password" type="password" required />
		<input class="rounded-2xl border-slate-300" name="password_confirmation" placeholder="Confirm password" type="password" required />
		<button class="btn-primary md:col-span-2" type="submit">Create user</button>
	</form>
	<div class="space-y-4">
		<div class="flex items-center justify-between">
			<div>
				<h2 class="text-xl font-semibold">Active users</h2>
				<p class="mt-1 text-sm text-slate-600">{visibleUsers(data.active).length} shown</p>
			</div>
		</div>
		{#if visibleUsers(data.active).length}
			{#each visibleUsers(data.active) as user}
				{#if editingUserId === user.id}
					<form class="panel grid gap-3 lg:grid-cols-[1fr_1fr_0.9fr_0.9fr_auto_auto]" method="POST" action="?/update">
						<input name="user_id" type="hidden" value={user.id} />
						<input class="rounded-2xl border-slate-300" name="name" value={user.name} required />
						<input class="rounded-2xl border-slate-300" name="email" type="email" value={user.email} required />
						<input class="rounded-2xl border-slate-300" name="password" placeholder="New password" type="password" />
						<input class="rounded-2xl border-slate-300" name="password_confirmation" placeholder="Confirm" type="password" />
						<button class="btn-secondary rounded-full px-4 py-2 text-sm" type="submit">Save</button>
						<button class="btn-secondary rounded-full px-4 py-2 text-sm" onclick={() => (editingUserId = null)} type="button">Cancel</button>
					</form>
				{:else}
					<div class="panel flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
						<div>
							<h3 class="text-xl font-semibold">{user.name}</h3>
							<p class="mt-1 text-sm text-slate-600">{user.email}</p>
						</div>
						<div class="flex gap-3">
							<button class="btn-secondary" onclick={() => (editingUserId = user.id)} type="button">Edit</button>
							<form method="POST" action="?/delete">
								<input name="user_id" type="hidden" value={user.id} />
								<button class="btn-danger" type="submit">Deactivate</button>
							</form>
						</div>
					</div>
				{/if}
			{/each}
		{:else}
			<div class="panel text-sm text-slate-600">No active users match the current search.</div>
		{/if}
	</div>
	<div class="space-y-4">
		<div class="flex items-center justify-between">
			<div>
				<h2 class="text-xl font-semibold">Deactivated users</h2>
				<p class="mt-1 text-sm text-slate-600">{visibleUsers(data.deactivated).length} shown</p>
			</div>
		</div>
		{#if visibleUsers(data.deactivated).length}
			{#each visibleUsers(data.deactivated) as user}
				<div class="panel flex flex-col gap-4 border-slate-200 bg-slate-50/80 lg:flex-row lg:items-center lg:justify-between">
					<div>
						<h3 class="text-xl font-semibold text-slate-800">{user.name}</h3>
						<p class="mt-1 text-sm text-slate-600">{user.email}</p>
						{#if user.deleted_at}
							<p class="mt-2 text-xs uppercase tracking-[0.24em] text-slate-500">Deactivated {new Date(user.deleted_at).toLocaleString()}</p>
						{/if}
					</div>
					<form method="POST" action="?/restore">
						<input name="user_id" type="hidden" value={user.id} />
						<button class="btn-secondary" type="submit">Reactivate</button>
					</form>
				</div>
			{/each}
		{:else}
			<div class="panel text-sm text-slate-600">No deactivated users match the current search.</div>
		{/if}
	</div>
</section>
