<script lang="ts">
	import { goto } from '$app/navigation';
	import { itemImageSrc } from '$lib/media';
	import Pagination from '$lib/components/Pagination.svelte';
	import { onDestroy } from 'svelte';

	let { data, form } = $props();
	let search = $state('');
	let sort = $state('newest');
	let searchDebounce: ReturnType<typeof setTimeout> | null = null;

	const paginationBasePath = $derived.by(() => {
		const params = new URLSearchParams();

		if (data.filters.search) {
			params.set('search', data.filters.search);
		}

		if (data.filters.sort && data.filters.sort !== 'newest') {
			params.set('sort', data.filters.sort);
		}

		const query = params.toString();
		return query ? `/admin/items?${query}` : '/admin/items';
	});

	$effect(() => {
		search = data.filters.search;
		sort = data.filters.sort;
	});

	onDestroy(() => {
		if (searchDebounce) {
			clearTimeout(searchDebounce);
		}
	});

	function buildItemsUrl(nextSearch: string, nextSort: string) {
		const params = new URLSearchParams();
		const trimmedSearch = nextSearch.trim();

		if (trimmedSearch) {
			params.set('search', trimmedSearch);
		}

		if (nextSort && nextSort !== 'newest') {
			params.set('sort', nextSort);
		}

		const query = params.toString();
		return query ? `/admin/items?${query}` : '/admin/items';
	}

	async function applyFilters() {
		await goto(buildItemsUrl(search, sort), {
			replaceState: true,
			noScroll: true,
			keepFocus: true,
			invalidateAll: true
		});
	}

	function handleSearchInput() {
		if (searchDebounce) {
			clearTimeout(searchDebounce);
		}

		searchDebounce = setTimeout(() => {
			void applyFilters();
		}, 250);
	}

	function handleSortChange() {
		if (searchDebounce) {
			clearTimeout(searchDebounce);
		}

		void applyFilters();
	}
</script>

<section class="space-y-6">
	<div class="flex items-center justify-between gap-4">
		<div class="panel flex-1">
			<p class="eyebrow">Admin</p>
			<h1 class="mt-2 text-3xl font-semibold">Manage items</h1>
		</div>
		<a class="btn-primary rounded-full px-5 py-3 text-sm" href="/admin/items/create">Create item</a>
	</div>

	<form class="panel grid gap-4 lg:grid-cols-[minmax(0,1fr)_14rem_auto] lg:items-end" onsubmit={(event) => event.preventDefault()}>
		<label class="block">
			<span class="mb-1 block text-sm font-medium text-slate-700">Search items</span>
			<input
				bind:value={search}
				class="w-full rounded-2xl border-slate-300"
				name="search"
				type="search"
				placeholder="Search by item name or description"
				oninput={handleSearchInput}
			/>
		</label>
		<label class="block">
			<span class="mb-1 block text-sm font-medium text-slate-700">Sort by</span>
			<select bind:value={sort} class="w-full rounded-2xl border-slate-300" name="sort" onchange={handleSortChange}>
				<option value="newest">Newest first</option>
				<option value="oldest">Oldest first</option>
				<option value="name_asc">Name A-Z</option>
				<option value="name_desc">Name Z-A</option>
				<option value="price_low">Price low to high</option>
				<option value="price_high">Price high to low</option>
				<option value="stock_low">Stock low to high</option>
				<option value="stock_high">Stock high to low</option>
			</select>
		</label>
		<div class="flex flex-wrap gap-3">
			<button class="btn-primary rounded-full px-5 py-3 text-sm" type="button" onclick={() => void applyFilters()}>Apply</button>
			<a class="btn-secondary rounded-full px-5 py-3 text-sm" href="/admin/items">Reset</a>
		</div>
	</form>

	{#if form?.error}
		<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
	{/if}
	{#if form?.success}
		<p class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{form.success}</p>
	{/if}

	<div class="min-h-[32rem]">
		{#if data.items.data.length === 0}
			<div class="panel py-10 text-center">
				<p class="text-lg font-medium text-slate-800">No items matched the current filters.</p>
				<p class="mt-2 text-sm text-slate-500">Try a different search term or change the sort order.</p>
			</div>
		{:else}
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
		{/if}
	</div>

	<Pagination basePath={paginationBasePath} meta={data.items.meta} />
</section>
