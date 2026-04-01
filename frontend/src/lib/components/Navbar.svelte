<script lang="ts">
    import { user } from '$lib/stores/auth';
    import { cart } from '$lib/stores/cart';
    import { derived } from 'svelte/store';

    const cartCount = derived(cart, $c => $c.reduce((sum, i) => sum + i.quantity, 0));
</script>

<nav class="sticky top-0 z-40 flex items-center justify-between border-b border-slate-100 bg-white/80 px-8 py-4 backdrop-blur-md">
    <div class="flex items-center gap-8">
        <a class="text-sm font-medium tracking-wide text-slate-500 transition-colors hover:text-slate-900" href="/">Home</a>
        <a class="text-sm font-medium tracking-wide text-slate-500 transition-colors hover:text-slate-900" href="/shop">Shop</a>
    </div>

    <div class="flex items-center gap-6">
        <a class="relative flex items-center gap-2 text-slate-500 transition-colors hover:text-slate-900" href="/cart">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            {#if $cartCount > 0}
                <span class="absolute -right-1.5 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-slate-900 text-[9px] font-bold text-white shadow-sm">
                    {$cartCount}
                </span>
            {/if}
        </a>

        <div class="h-4 w-px bg-slate-200"></div>

        {#if $user}
            <div class="flex items-center gap-5">
                <span class="text-sm font-medium text-slate-700">{$user.name}</span>
                <a class="text-sm font-medium text-slate-500 transition-colors hover:text-slate-900" href="/orders">Orders</a>
                <a class="text-sm font-medium text-rose-400 transition-colors hover:text-rose-600" href="/logout">Logout</a>
            </div>
        {:else}
            <div class="flex items-center gap-4">
                <a class="text-sm font-medium text-slate-500 transition-colors hover:text-slate-900" href="/login">Login</a>
                <a class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium tracking-wide text-white transition-all hover:bg-slate-800 hover:shadow-md" href="/register">Register</a>
            </div>
        {/if}
    </div>
</nav>