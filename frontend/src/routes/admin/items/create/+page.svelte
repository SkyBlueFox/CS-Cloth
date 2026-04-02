<script lang="ts">
	let { form } = $props();
</script>

<section class="mx-auto max-w-3xl">
	<form class="panel space-y-4" method="POST" enctype="multipart/form-data">
		<div>
			<p class="eyebrow">Admin</p>
			<h1 class="mt-2 text-3xl font-semibold">Create item</h1>
		</div>
		{#if form?.error}
			<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
		{/if}
		<label class="block">
			<span class="mb-1 block text-sm font-medium">Name</span>
			<input class="w-full rounded-2xl border-slate-300" name="name" required />
		</label>
		<label class="block">
			<span class="mb-1 block text-sm font-medium">Description</span>
			<textarea class="w-full rounded-2xl border-slate-300" name="description" rows="5"></textarea>
		</label>
		<div class="grid gap-4 md:grid-cols-2">
			<label class="block">
				<span class="mb-1 block text-sm font-medium">Price</span>
				<input class="w-full rounded-2xl border-slate-300" name="price" min="0" step="0.01" type="number" required />
			</label>
			<label class="block">
				<span class="mb-1 block text-sm font-medium">Stock</span>
				<input class="w-full rounded-2xl border-slate-300" name="stock" min="0" type="number" required />
			</label>
		</div>
		<label class="block">
			<span class="mb-1 block text-sm font-medium">Image</span>
			<input class="w-full rounded-2xl border-slate-300" name="image" type="file" accept="image/*" />
		</label>
		<label class="flex items-center gap-3 text-sm font-medium">
			<input checked name="is_active" type="checkbox" value="1" />
			Active
		</label>
		<button class="btn-primary" type="submit">Create item</button>
	</form>
</section>
