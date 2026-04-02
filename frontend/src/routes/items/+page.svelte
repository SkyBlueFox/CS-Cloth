<script lang="ts">
	import { itemImageSrc } from '$lib/media';
	import Pagination from '$lib/components/Pagination.svelte';
	import { fly } from 'svelte/transition';
	let { data } = $props();
</script>

<section class="flex flex-col gap-12">
	<header class="relative overflow-hidden rounded-[3rem] border border-slate-200 bg-white p-10 shadow-sm sm:p-16">
		<div class="relative z-10 flex flex-col gap-5">
			<div class="flex items-center gap-3">
				<span class="h-1.5 w-10 rounded-full bg-blue-600"></span>
				<p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600">Premium Marketplace</p>
			</div>
			<h1 class="max-w-2xl text-4xl font-black tracking-tight text-slate-900 sm:text-6xl">
				Official merchandise for <span class="text-blue-600 italic">CS Cloth.</span>
			</h1>
			<p class="max-w-lg text-base font-bold uppercase tracking-wide leading-relaxed text-slate-600">
				Explore our curated collection of high-quality apparel. Real-time stock tracking enabled.
			</p>
		</div>
		<div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-blue-50/50 blur-[100px]"></div>
	</header>

	<div class="grid grid-cols-1 gap-10 md:grid-cols-2 xl:grid-cols-3">
		{#each data.items.data as item (item.id)}
			<a
				class="group relative flex flex-col rounded-[3rem] border border-transparent bg-white p-4 shadow-sm transition-all duration-500 hover:-translate-y-2 hover:border-blue-100 hover:shadow-2xl hover:shadow-blue-900/10"
				href={`/items/${item.id}`}
				in:fly={{ y: 20, duration: 400 }}
			>
				<div class="relative aspect-[4/3] w-full overflow-hidden rounded-[2.5rem] bg-slate-100 shadow-inner">
					{#if itemImageSrc(item)}
						<img
							class="h-full w-full object-cover transition-transform duration-1000 group-hover:scale-110"
							src={itemImageSrc(item) ?? undefined}
							alt={item.name}
						/>
					{:else}
						<div class="flex h-full flex-col items-center justify-center gap-3 text-slate-400">
							<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
							</svg>
							<span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-50">No Preview</span>
						</div>
					{/if}

					<div class="absolute left-6 top-6 rounded-full bg-white/90 px-4 py-1.5 text-[10px] font-black text-slate-900 shadow-xl ring-1 ring-black/5 backdrop-blur-md">
						AVAIL: {item.stock}
					</div>
				</div>

				<div class="flex flex-1 flex-col px-4 py-8">
					<div class="mb-8 flex-1">
						<h2 class="text-2xl font-black tracking-tight text-slate-900 transition-colors group-hover:text-blue-700">
							{item.name}
						</h2>
						<p class="mt-3 line-clamp-2 text-sm font-bold uppercase tracking-tight leading-relaxed text-slate-500">
							{item.description}
						</p>
					</div>

					<div class="flex items-center justify-between border-t border-slate-50 pt-8">
						<div class="flex flex-col">
							<span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Net Price</span>
							<p class="text-3xl font-black tracking-tighter text-slate-900">
								฿{item.price.toLocaleString(undefined, { minimumFractionDigits: 2 })}
							</p>
						</div>

						<div class="flex h-14 w-14 items-center justify-center rounded-[1.5rem] bg-slate-900 text-white shadow-xl shadow-slate-900/20 transition-all duration-300 group-hover:rotate-12 group-hover:bg-blue-600 group-hover:shadow-blue-600/40">
							<svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
								<path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
							</svg>
						</div>
					</div>
				</div>
			</a>
		{/each}
	</div>

	<footer class="mt-10 flex justify-center border-t border-slate-100 py-16">
		<Pagination basePath="/items" meta={data.items.meta} />
	</footer>
</section>
