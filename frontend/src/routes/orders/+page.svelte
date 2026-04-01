<script lang="ts">
    import { itemImageSrc } from '$lib/media';
    import Pagination from '$lib/components/Pagination.svelte';
    import { fly } from 'svelte/transition';
    import { page } from '$app/stores';

    let { data, form } = $props();

    const paginationBasePath = $derived(() => {
        const url = new URL($page.url);
        url.searchParams.delete('page');
        const search = url.searchParams.toString();
        return `/orders${search ? '?' + search : ''}`;
    });

    function formatDate(date: string) {
        return new Date(date).toLocaleDateString('th-TH', {
            year: 'numeric', month: 'short', day: 'numeric'
        });
    }

    function getStatusColor(status: string) {
        switch(status) {
            case 'pending': return 'bg-amber-50 text-amber-700 ring-amber-200';
            case 'shipped': return 'bg-blue-50 text-blue-700 ring-blue-200';
            case 'completed': return 'bg-emerald-50 text-emerald-700 ring-emerald-200';
            case 'cancelled': return 'bg-rose-50 text-rose-700 ring-rose-200';
            default: return 'bg-slate-50 text-slate-700 ring-slate-200';
        }
    }

    // Create properly typed pagination meta with safe property access
    const paginationMeta = $derived({
        total: data.orders.meta.total,
        current_page: data.orders.meta.current_page,
        last_page: data.orders.meta.last_page,
        per_page: 'per_page' in data.orders.meta ? data.orders.meta.per_page : 10
    });
</script>

<section class="mx-auto max-w-6xl space-y-10">
    <header class="relative overflow-hidden rounded-[3rem] bg-white border border-slate-200 p-10 shadow-sm">
        <div class="relative z-10 flex flex-col gap-3">
            <div class="flex items-center gap-3">
                <span class="h-1.5 w-10 rounded-full bg-blue-600"></span>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600">Transactions</p>
            </div>
            <h1 class="text-4xl font-black tracking-tight text-slate-900">Purchase History</h1>
            <p class="max-w-md text-sm font-bold leading-relaxed text-slate-600 uppercase tracking-wide">
                Manage your orders, track shipments and handle returns.
            </p>
        </div>
        <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-slate-50 blur-[80px]"></div>
    </header>

    <form class="flex flex-col gap-4 md:flex-row" method="GET" action="/orders">
        <div class="relative flex-1">
            <svg class="absolute left-5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2.5" stroke-linecap="round"/></svg>
            <input
                class="w-full rounded-2xl border-slate-200 bg-white pl-14 pr-6 py-4 text-sm font-bold text-slate-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"
                name="search"
                placeholder="Search by ID or Item name..."
                type="search"
                value={data.filters.search}
            />
        </div>
        <div class="flex gap-3">
            <button class="btn-primary px-8" type="submit">Filter</button>
            <a class="btn-secondary flex items-center px-8" href="/orders">Reset</a>
        </div>
    </form>

    {#if form?.error || form?.success}
        <div in:fly={{ y: -10 }} class="rounded-2xl shadow-sm overflow-hidden">
            {#if form?.error} <p class="bg-rose-50 px-6 py-4 text-sm font-black text-rose-800 ring-1 ring-rose-200">{form.error}</p> {/if}
            {#if form?.success} <p class="bg-emerald-50 px-6 py-4 text-sm font-black text-emerald-800 ring-1 ring-emerald-200">{form.success}</p> {/if}
        </div>
    {/if}

    <div class="space-y-8">
        {#each data.orders.data as order (order.id)}
            <article class="group rounded-[3rem] bg-white border border-slate-100 shadow-sm transition-all duration-500 hover:shadow-2xl hover:shadow-blue-900/5 hover:border-blue-100">
                <div class="p-8 sm:p-10">
                    <div class="mb-8 flex flex-col justify-between gap-6 border-b border-slate-50 pb-8 sm:flex-row sm:items-center">
                        <div class="space-y-1">
                            <a class="text-xl font-black text-slate-900 transition-colors group-hover:text-blue-700 tracking-tight uppercase" href={`/orders/${order.id}`}>
                                ID: {order.order_number}
                            </a>
                            <div class="flex flex-wrap items-center gap-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                                {#if order.created_at}
                                    <span>{formatDate(order.created_at)}</span>
                                {/if}
                                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                <span class="text-slate-900 font-black">฿{order.total_price.toLocaleString()}</span>
                            </div>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full px-5 py-2 text-[10px] font-black uppercase tracking-widest ring-1 {getStatusColor(order.status)}">
                                {order.status.replace('_', ' ')}
                            </span>
                            <a class="rounded-xl bg-slate-50 px-6 py-2.5 text-[11px] font-black uppercase tracking-widest text-slate-600 transition-all hover:bg-slate-900 hover:text-white" href={`/orders/${order.id}`}>
                                Details
                            </a>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {#each order.items as line (line.id)}
                            <div class="flex items-center gap-4 rounded-2xl bg-slate-50/50 p-4 ring-1 ring-slate-100 transition-all hover:bg-white hover:shadow-md">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                                    {#if line.item}
                                        <img alt="" class="h-full w-full object-cover" src={itemImageSrc(line.item)} />
                                    {/if}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-slate-900 tracking-tight">{line.item?.name}</p>
                                    <p class="text-[11px] font-bold text-slate-500 uppercase">Qty: {line.quantity}</p>
                                </div>
                            </div>
                        {/each}
                    </div>
                </div>
            </article>
        {/each}
    </div>

    <Pagination basePath={paginationBasePath()} meta={paginationMeta} />
</section>