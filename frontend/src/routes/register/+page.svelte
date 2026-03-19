<script lang="ts">
	let { form } = $props();
</script>

<section class="mx-auto max-w-2xl">
	<form class="panel space-y-4" method="POST">
		<div>
			<p class="eyebrow">Register</p>
			<h1 class="mt-2 text-3xl font-semibold">Create a user account</h1>
		</div>
		{#if form?.error}
			<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
		{/if}
		<label class="block">
			<span class="mb-1 block text-sm font-medium">Name</span>
			<input class="w-full rounded-2xl border-slate-300" name="name" value={form?.name ?? ''} required />
		</label>
		<label class="block">
			<span class="mb-1 block text-sm font-medium">Email</span>
			<input class="w-full rounded-2xl border-slate-300" name="email" type="email" value={form?.email ?? ''} required />
		</label>
		<label class="block">
			<span class="mb-1 block text-sm font-medium">Password</span>
			<input class="w-full rounded-2xl border-slate-300" name="password" type="password" required />
		</label>
		<label class="block">
			<span class="mb-1 block text-sm font-medium">Confirm password</span>
			<input class="w-full rounded-2xl border-slate-300" name="password_confirmation" type="password" required />
		</label>
		<button class="btn-primary w-full" type="submit">Create account</button>
	</form>
</section>
