<script lang="ts">
	import { itemImageSrc } from '$lib/media';
	import Pagination from '$lib/components/Pagination.svelte';

	let { data, form } = $props();
</script>

<section class="space-y-6">
	<div class="flex items-center justify-between gap-4">
		<div class="panel flex-1">
			<p class="eyebrow">Admin</p>
			<h1 class="mt-2 text-3xl font-semibold">Manage items</h1>
		</div>
		<a class="btn-primary rounded-full px-5 py-3 text-sm" href="/admin/items/create">Create item</a>
	</div>

	{#if form?.error}
		<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
	{/if}
	{#if form?.success}
		<p class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{form.success}</p>
	{/if}

	<div class="grid gap-5">
		{#each data.items.data as item}
			<article class="panel flex flex-col gap-5 lg:flex-row lg:items-center">
				{#if itemImageSrc(item)}
					<img class="h-28 w-28 rounded-[1.5rem] object-cover" src={itemImageSrc(item) ?? undefined} alt={item.name} />
				{:else}
					<div class="subtle-box flex h-28 w-28 items-center justify-center text-sm text-sky-500">No image</div>
				{/if}
				<div class="flex-1">
					<div class="flex flex-wrap items-center gap-3">
						<h2 class="text-xl font-semibold">{item.name}</h2>
						<span class={`rounded-full px-3 py-1 text-xs font-medium ${item.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700'}`}>
							{item.is_active ? 'Active' : 'Hidden'}
						</span>
					</div>
					<p class="mt-2 text-sm text-slate-600">{item.description}</p>
					<div class="mt-3 flex gap-4 text-sm text-slate-500">
						<span>฿{item.price.toFixed(2)}</span>
						<span>{item.stock} in stock</span>
					</div>
				</div>
				<div class="flex flex-wrap gap-3">
					<a class="btn-secondary rounded-full px-4 py-2 text-sm" href={`/admin/items/${item.id}/edit`}>Edit</a>
					<form method="POST" action="?/toggle">
						<input name="item_id" type="hidden" value={item.id} />
						<button class="rounded-full border border-amber-300 px-4 py-2 text-sm text-amber-700" type="submit">Toggle</button>
					</form>
					<form method="POST" action="?/delete">
						<input name="item_id" type="hidden" value={item.id} />
						<button class="rounded-full border border-rose-300 px-4 py-2 text-sm text-rose-700" type="submit">Delete</button>
					</form>
				</div>
			</article>
		{/each}
	</div>

	<Pagination basePath="/admin/items" meta={data.items.meta} />
</section>
