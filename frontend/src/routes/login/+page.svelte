<script lang="ts">
	let { form } = $props();
</script>

<section class="mx-auto grid max-w-5xl gap-8 lg:grid-cols-[1.2fr_0.8fr]">
	<div class="panel hero-panel">
		<p class="eyebrow text-sky-100">CS Cloth</p>
		<h1 class="mt-4 text-4xl font-semibold">Sign in to manage your account.</h1>
		<p class="mt-4 max-w-xl text-sky-100/90">
			Access the shop, track your orders, answer customer questions, and manage the store from one place.
		</p>
	</div>

	<form class="panel space-y-4" method="POST">
		<div>
			<p class="eyebrow">Login</p>
			<h2 class="mt-2 text-2xl font-semibold">Use your existing account</h2>
		</div>
		{#if form?.error}
			<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
		{/if}
		<label class="block">
			<span class="mb-1 block text-sm font-medium">Email</span>
			<input class="w-full rounded-2xl border-slate-300" name="email" type="email" value={form?.email ?? ''} required />
		</label>
		<label class="block">
			<span class="mb-1 block text-sm font-medium">Password</span>
			<input class="w-full rounded-2xl border-slate-300" name="password" type="password" required />
		</label>
		<button class="btn-primary w-full" type="submit">Login</button>
		<p class="text-sm text-slate-500">
			No account yet? <a class="font-medium text-sky-700" href="/register">Register here</a>.
		</p>
	</form>
</section>
