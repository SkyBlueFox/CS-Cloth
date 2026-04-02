<script lang="ts">
    import { itemImageSrc } from '$lib/media';
    import { fly, fade } from 'svelte/transition';
    import { enhance } from '$app/forms';
    import type { PageData, ActionData } from './$types';

    let { data, form }: { data: PageData, form: ActionData } = $props();
    
    interface CartItem {
        id: number;
        name: string;
        price: number | string;
        quantity: number;
        image_path: string | null;
        image_url: string | null;
        stock?: number;
    }

    // ดึงค่า cart และคำนวณราคารวม (เพิ่ม Type safety สำหรับ i)
    const cart = $derived((data.cart as CartItem[]) ?? []);
    const totalPrice = $derived(cart.reduce((sum: number, i: CartItem) => sum + (Number(i.price) * i.quantity), 0));
    const errorMessage = $derived(form?.error || data?.error);
    const itemsOverStock = $derived(cart.filter((item) => typeof item.stock === 'number' && item.quantity > item.stock));
    const hasStockMismatch = $derived(itemsOverStock.length > 0);
</script>

<section class="mx-auto max-w-5xl space-y-10">
    <header class="relative overflow-hidden rounded-[3rem] bg-white border border-slate-200 p-10 shadow-sm">
        <div class="relative z-10 flex flex-col gap-3">
            <div class="flex items-center gap-3">
                <span class="h-1.5 w-10 rounded-full bg-blue-600"></span>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600">Shopping Bag</p>
            </div>
            <h1 class="text-4xl font-black tracking-tight text-slate-900">My Cart</h1>
            <p class="max-w-md text-sm font-bold leading-relaxed text-slate-600 uppercase tracking-wide">
                Review your selected items and proceed to secure checkout.
            </p>
        </div>
        <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-slate-50 blur-[80px]"></div>
    </header>

    {#if errorMessage || form?.success}
        <div in:fly={{ y: -10 }} class="rounded-[1.5rem] shadow-sm overflow-hidden border border-slate-100">
            {#if errorMessage}
                <p class="bg-rose-50 px-8 py-5 text-sm font-black text-rose-800 ring-1 ring-rose-200">{errorMessage}</p>
            {/if}
            {#if form?.success}
                <p class="bg-emerald-50 px-8 py-5 text-sm font-black text-emerald-800 ring-1 ring-emerald-200">{form.success}</p>
            {/if}
        </div>
    {/if}
    {#if hasStockMismatch}
        <div class="rounded-[1.5rem] overflow-hidden border border-amber-200 shadow-sm">
            <div class="bg-amber-50 px-8 py-5 text-sm font-black text-amber-900 ring-1 ring-amber-200">
                Some cart quantities are higher than current stock. Lower them before checkout.
            </div>
        </div>
    {/if}

    {#if cart.length === 0}
        <div class="panel py-24 text-center flex flex-col items-center justify-center border-dashed border-2 border-slate-200 bg-transparent shadow-none" in:fade>
            <div class="mb-6 rounded-full bg-slate-100 p-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <h2 class="text-xl font-black text-slate-900 uppercase">Your cart is empty</h2>
            <p class="mt-2 text-sm font-bold text-slate-500 uppercase tracking-widest">Explore our store and add some items</p>
            <a href="/items" class="btn-primary mt-8 px-10">Start Shopping</a>
        </div>
    {:else}
        <div class="grid gap-6">
            {#each cart as item (item.id)}
                <div class="panel group flex flex-col sm:flex-row items-center gap-8 p-8 transition-all hover:shadow-2xl hover:shadow-blue-900/5 hover:border-blue-100">
                    
                    <div class="h-32 w-32 shrink-0 overflow-hidden rounded-[1.5rem] bg-white shadow-md ring-1 ring-slate-200">
                        <img 
                            class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" 
                            src={itemImageSrc(item)} 
                            alt={item.name} 
                        />
                    </div>

                    <div class="flex-1 text-center sm:text-left space-y-1">
                        <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase truncate">{item.name}</h2>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                            <p class="text-sm font-bold text-slate-500 uppercase tracking-widest">Unit: ฿{Number(item.price).toLocaleString()}</p>
                            <span class="hidden sm:block text-slate-300">|</span>
                            <p class="text-xl font-black text-blue-700">Total: ฿{(Number(item.price) * item.quantity).toLocaleString()}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-6 border-t border-slate-50 pt-6 sm:border-none sm:pt-0">
                        <form method="POST" action="?/update" use:enhance class="flex items-center gap-3 rounded-2xl bg-slate-100 p-1.5 ring-1 ring-slate-200">
                            <input type="hidden" name="item_id" value={item.id} />
                            <input 
                                type="number" 
                                name="quantity" 
                                min="1" 
                                max={item.stock && item.stock > 0 ? item.stock : undefined}
                                class="w-20 rounded-xl border-none bg-white px-3 py-2 text-center font-black text-slate-900 focus:ring-2 focus:ring-blue-500/20" 
                                value={item.quantity} 
                                onchange={(event) => event.currentTarget.form?.requestSubmit()}
                            />
                            <span class="px-2 text-[10px] font-black uppercase tracking-widest text-slate-500">
                                Auto-saves
                            </span>
                        </form>
                        
                        <form method="POST" action="?/remove" use:enhance>
                            <input type="hidden" name="item_id" value={item.id} />
                            <button aria-label={`Remove ${item.name} from cart`} title="Remove item" type="submit" class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 transition-all hover:bg-rose-600 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                    <div class="w-full border-t border-slate-100 pt-4 text-center sm:text-left">
                        {#if typeof item.stock === 'number' && item.stock === 0}
                            <p class="text-sm font-black uppercase tracking-widest text-rose-600">
                                Out of stock. Remove this item to continue.
                            </p>
                        {:else if typeof item.stock === 'number' && item.quantity > item.stock}
                            <p class="text-sm font-black uppercase tracking-widest text-amber-600">
                                Only {item.stock} left in stock. Reduce quantity to continue.
                            </p>
                        {:else if typeof item.stock === 'number'}
                            <p class="text-sm font-black uppercase tracking-widest text-slate-400">
                                {item.stock} currently available
                            </p>
                        {/if}
                    </div>
                </div>
            {/each}
        </div>

        <div class="mt-10 flex flex-col sm:flex-row items-center justify-between gap-8 rounded-[2.5rem] bg-slate-900 p-10 text-white shadow-2xl shadow-slate-900/20">
            <div class="text-center sm:text-left">
                <p class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-400">Grand Total</p>
                <p class="mt-1 text-5xl font-black tracking-tighter">
                    ฿{totalPrice.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                <a href="/items" class="btn-secondary bg-white/10 border-transparent text-white hover:bg-white/20 px-8 py-5 text-center">Continue Shopping</a>
                <a
                    aria-disabled={hasStockMismatch}
                    class={`px-12 py-5 text-center ${
                        hasStockMismatch
                            ? 'cursor-not-allowed rounded-2xl bg-slate-500 text-white opacity-60'
                            : 'btn-primary border-none bg-blue-600 shadow-xl shadow-blue-600/20 hover:bg-blue-500'
                    }`}
                    href={hasStockMismatch ? undefined : '/checkout'}
                >
                    {hasStockMismatch ? 'Adjust Quantities First' : 'Secure Checkout'}
                </a>
            </div>
        </div>
    {/if}
</section>
