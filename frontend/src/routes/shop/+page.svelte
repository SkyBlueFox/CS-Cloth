<script lang="ts">
	import Pagination from '$lib/components/Pagination.svelte';
	let { data } = $props();
</script>

<section class="space-y-8">
	<div class="panel hero-panel overflow-hidden">
		<p class="eyebrow text-sky-100">Shop</p>
		<h1 class="mt-3 max-w-2xl text-4xl font-semibold">Official merchandise for the CS Cloth store.</h1>
		<p class="mt-4 max-w-2xl text-sky-100/90">Browse available items, inspect details, ask questions, and place orders directly from the storefront.</p>
	</div>

	<div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
		{#each data.items.data as item (item.id)}
			<a class="panel flex flex-col gap-4 transition hover:-translate-y-1" href={`/shop/${item.id}`}>
				{#if item.image_url}
					<img class="h-56 w-full rounded-3xl object-cover" src={item.image_url} alt={item.name} />
				{:else}
					<div class="subtle-box flex h-56 items-center justify-center text-sm text-sky-500">No image</div>
				{/if}
				<div class="space-y-2">
					<h2 class="text-xl font-semibold">{item.name}</h2>
					<p class="line-clamp-3 text-sm text-slate-600">{item.description}</p>
				</div>
				<div class="mt-auto flex items-end justify-between">
					<p class="text-2xl font-semibold">฿{item.price.toFixed(2)}</p>
					<p class="text-sm text-slate-500">{item.stock} in stock</p>
				</div>
			</a>
		{/each}
	</div>

	<Pagination basePath="/shop" meta={data.items.meta} />
</section>