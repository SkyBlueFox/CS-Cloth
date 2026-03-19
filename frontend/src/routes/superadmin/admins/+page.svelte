<script lang="ts">
	let { data, form } = $props();
</script>

<section class="space-y-8">
	<div class="panel">
		<p class="eyebrow">Superadmin</p>
		<h1 class="mt-2 text-3xl font-semibold">Manage admins</h1>
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
		{#each data.data as admin}
			<form class="panel grid gap-3 lg:grid-cols-[1fr_1fr_0.9fr_0.9fr_auto_auto]" method="POST" action="?/update">
				<input name="user_id" type="hidden" value={admin.id} />
				<input class="rounded-2xl border-slate-300" name="name" value={admin.name} required />
				<input class="rounded-2xl border-slate-300" name="email" type="email" value={admin.email} required />
				<input class="rounded-2xl border-slate-300" name="password" placeholder="New password" type="password" />
				<input class="rounded-2xl border-slate-300" name="password_confirmation" placeholder="Confirm" type="password" />
				<button class="btn-secondary rounded-full px-4 py-2 text-sm" type="submit">Save</button>
				<button class="rounded-full border border-rose-300 px-4 py-2 text-sm text-rose-700" formaction="?/delete" type="submit">Delete</button>
			</form>
		{/each}
	</div>
</section>
