<script lang="ts">
    import { itemImageSrc } from '$lib/media';
    import Pagination from '$lib/components/Pagination.svelte';
    import {  fly } from 'svelte/transition';
    let { data } = $props();
</script>

<section class="flex flex-col gap-10">
    <header class="relative overflow-hidden rounded-[2.5rem] bg-white border border-slate-200 p-8 sm:p-12 shadow-sm">
        <div class="relative z-10 flex flex-col gap-4">
            <div class="flex items-center gap-2">
                <span class="h-1 w-8 rounded-full bg-blue-600"></span>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Marketplace</p>
            </div>
            <h1 class="max-w-xl text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                Official merchandise for the <span class="text-blue-600">CS Cloth</span> store.
            </h1>
            <p class="max-w-lg text-sm leading-relaxed text-slate-500">
                Explore our curated collection of high-quality apparel. Direct ordering and real-time stock tracking enabled.
            </p>
        </div>
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-blue-50 blur-3xl"></div>
    </header>

    <div class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
        {#each data.items.data as item (item.id)}
            <a 
                class="group relative flex flex-col rounded-[2rem] border border-transparent bg-white p-3 shadow-sm transition-all duration-500 hover:border-blue-100 hover:shadow-xl hover:shadow-blue-900/5" 
                href={`/shop/${item.id}`}
                in:fly={{ y: 20, duration: 400 }}
            >
                <div class="relative aspect-[4/3] w-full overflow-hidden rounded-[1.6rem] bg-slate-100">
                    {#if itemImageSrc(item)}
                        <img 
                            class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" 
                            src={itemImageSrc(item) ?? undefined} 
                            alt={item.name} 
                        />
                    {:else}
                        <div class="flex h-full flex-col items-center justify-center gap-2 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-[10px] font-bold uppercase tracking-widest opacity-40">No Image</span>
                        </div>
                    {/if}
                    
                    <div class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-[10px] font-bold text-slate-900 shadow-sm backdrop-blur">
                        STK: {item.stock}
                    </div>
                </div>

                <div class="flex flex-1 flex-col px-3 py-5">
                    <div class="mb-6 flex-1">
                        <h2 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors">
                            {item.name}
                        </h2>
                        <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-slate-400">
                            {item.description}
                        </p>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold uppercase tracking-tighter text-slate-300">Net Price</span>
                            <p class="text-2xl font-black tracking-tight text-slate-900">
                                ฿{item.price.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                            </p>
                        </div>
                        
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-900 text-white transition-all duration-300 group-hover:bg-blue-600 group-hover:shadow-lg group-hover:shadow-blue-600/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                    </div>
                </div>
            </a>
        {/each}
    </div>

    <footer class="mt-4 flex justify-center border-t border-slate-100 py-10">
        <Pagination basePath="/shop" meta={data.items.meta} />
    </footer>
</section>