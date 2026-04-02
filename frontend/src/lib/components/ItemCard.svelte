<script context="module" lang="ts">
    export type Item = {
        id: number;
        name: string;
        price: number;
        image_path?: string;
    };
</script>

<script lang="ts">
    import { addToCart } from '$lib/stores/cart';
    let { item } = $props<{ item: Item }>();
</script>

<div class="group flex flex-col overflow-hidden rounded-[2rem] border border-slate-100 bg-white p-4 transition-all duration-300 hover:shadow-xl">
    <div class="relative h-48 overflow-hidden rounded-[1.5rem] bg-slate-50">
        <img
            src={item.image_path || '/placeholder.png'}
            alt={item.name}
            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
        />
    </div>

    <div class="mt-4 flex flex-1 flex-col px-1">
        <h2 class="text-lg font-bold text-slate-900">{item.name}</h2>
        <p class="mt-1 text-xl font-black text-blue-700">฿{item.price.toLocaleString()}</p>

        <div class="mt-6 flex gap-2">
            <a 
                href={`/items/${item.id}`} 
                class="flex-1 rounded-xl bg-slate-50 py-2.5 text-center text-xs font-bold text-slate-600 transition-colors hover:bg-slate-100"
            >
                Details
            </a>
            <button 
                class="flex-1 rounded-xl bg-blue-600 py-2.5 text-center text-xs font-bold text-white shadow-md shadow-blue-600/20 transition-all hover:bg-blue-700 active:scale-95" 
                onclick={() => addToCart(item)}
            >
                Add to cart
            </button>
        </div>
    </div>
</div>
