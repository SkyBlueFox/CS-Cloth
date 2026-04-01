<script lang="ts">
    import { deliveryOptions } from '$lib/delivery';
    import { itemImageSrc } from '$lib/media';
    import { fly, fade } from 'svelte/transition';
    import { enhance } from '$app/forms';

    let { data, form } = $props();
    
    // ตั้งค่าเริ่มต้นให้ที่อยู่
    let selectedAddressId = $state('__init__');
    let selectedDelivery = $state('thailand_post');
    
    // คำนวณราคารวม (Grand Total)
    const subtotal = $derived(data.checkoutItems.reduce((sum, i) => sum + (Number(i.price) * i.quantity), 0));
    
    // เตรียมข้อมูล items สำหรับส่งไป Backend
    const itemsJson = $derived(JSON.stringify(data.checkoutItems.map(i => ({ 
        item_id: i.item_id, 
        quantity: i.quantity 
    }))));

    $effect(() => {
        if (selectedAddressId === '__init__') {
            selectedAddressId = data.addresses.find(a => a.is_default)?.id?.toString() ?? '';
        }
    });
</script>

<section class="mx-auto max-w-6xl space-y-10 pb-20">
    <header class="flex flex-col gap-2">
        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-blue-600">Secure Payment</p>
        <h1 class="text-4xl font-black tracking-tight text-slate-900 uppercase">Checkout</h1>
    </header>

    {#if form?.error}
        <div in:fly={{ y: -10 }} class="rounded-2xl bg-rose-50 p-6 text-rose-700 font-bold border border-rose-100 shadow-sm">
            {form.error}
        </div>
    {/if}

    <form method="POST" action="?/placeOrder" use:enhance class="grid gap-10 lg:grid-cols-[1fr_24rem]">
        <input type="hidden" name="items_json" value={itemsJson} />
        <input type="hidden" name="delivery_method" value={selectedDelivery} />

        <div class="space-y-8">
            <div class="panel p-10 space-y-8">
                <div class="flex items-center gap-4">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-white text-[10px] font-black">1</span>
                    <h2 class="text-xl font-black uppercase tracking-tight">Shipping Address</h2>
                </div>

                <select bind:value={selectedAddressId} name="address_id" class="w-full rounded-2xl border-slate-200 py-4 px-6 font-bold text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                    {#each data.addresses as addr (addr.id)}
                        <option value={addr.id.toString()}>{addr.label} — {addr.province}</option>
                    {/each}
                    <option value="">+ Use New Address</option>
                </select>

                {#if selectedAddressId === ''}
                    <div in:fade class="grid gap-4 sm:grid-cols-2 rounded-[2rem] bg-slate-50 p-8 ring-1 ring-slate-200">
                        <input name="recipient_name" placeholder="Recipient Name" class="rounded-xl border-slate-200 p-4 font-bold" />
                        <input name="phone" placeholder="Phone Number" class="rounded-xl border-slate-200 p-4 font-bold" />
                        <input name="line_1" placeholder="Address Detail (House No., Street)" class="col-span-2 rounded-xl border-slate-200 p-4 font-bold" />
                        <input name="province" placeholder="Province" class="rounded-xl border-slate-200 p-4 font-bold" />
                        <input name="postal_code" placeholder="Zip Code" class="rounded-xl border-slate-200 p-4 font-bold" />
                    </div>
                {/if}
            </div>

            <div class="panel p-10 space-y-8">
                <div class="flex items-center gap-4">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-white text-[10px] font-black">2</span>
                    <h2 class="text-xl font-black uppercase tracking-tight">Delivery Method</h2>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    {#each deliveryOptions as opt (opt.value)}
                        <button type="button" 
                            onclick={() => selectedDelivery = opt.value}
                            class="flex items-center gap-4 rounded-[2rem] border-2 p-6 transition-all {selectedDelivery === opt.value ? 'border-blue-600 bg-blue-50 shadow-lg shadow-blue-600/5' : 'border-slate-100 bg-white hover:border-slate-200'}">
                            <div class="h-12 w-12 shrink-0 rounded-xl bg-white p-2 ring-1 ring-slate-100">
                                <img src={opt.logo} alt="" class="h-full w-full object-contain" />
                            </div>
                            <div class="text-left">
                                <p class="font-black uppercase text-[10px] tracking-widest text-slate-900">{opt.label}</p>
                                <p class="text-[9px] font-bold text-slate-400 mt-0.5 uppercase tracking-tight">{opt.note}</p>
                            </div>
                        </button>
                    {/each}
                </div>
            </div>
        </div>

        <aside class="relative">
            <div class="sticky top-24 panel p-8 bg-white shadow-2xl border-none">
                <h3 class="text-[10px] font-black uppercase tracking-[0.3em] mb-8 text-slate-400">Order Summary</h3>
                
                <div class="space-y-6 max-h-[25rem] overflow-y-auto mb-8 pr-2 custom-scrollbar">
                    {#each data.checkoutItems as item (item.item_id)}
                        <div class="flex gap-4">
                            <div class="h-20 w-20 shrink-0 rounded-[1.25rem] bg-slate-50 ring-1 ring-slate-100 overflow-hidden shadow-sm">
                                <img src={itemImageSrc({ image_path: item.image_path, image_url: item.image_url })} alt="" class="h-full w-full object-cover" />
                            </div>
                            <div class="min-w-0 flex flex-col justify-center">
                                <p class="truncate text-sm font-black uppercase text-slate-900">{item.name}</p>
                                <p class="text-[10px] font-black text-blue-600 mt-1 uppercase tracking-widest">Qty: {item.quantity}</p>
                                <p class="text-sm font-black text-slate-900 mt-1">฿{(item.price * item.quantity).toLocaleString()}</p>
                            </div>
                        </div>
                    {:else}
                        <p class="text-center py-10 text-slate-400 font-bold uppercase text-xs">No items selected</p>
                    {/each}
                </div>

                <div class="space-y-4 pt-8 border-t border-slate-100">
                    <div class="flex justify-between items-center text-slate-500 font-bold uppercase text-[10px] tracking-widest">
                        <span>Subtotal</span>
                        <span>฿{subtotal.toLocaleString()}</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-900 font-black text-3xl tracking-tighter">
                        <span>Total</span>
                        <span class="text-blue-700">฿{subtotal.toLocaleString()}</span>
                    </div>
                </div>

                <button 
                    type="submit" 
                    disabled={data.checkoutItems.length === 0}
                    class="btn-primary w-full mt-10 py-5 bg-slate-900 hover:bg-blue-600 border-none shadow-xl shadow-slate-900/10 text-[10px] tracking-[0.3em] uppercase disabled:opacity-50 disabled:bg-slate-300"
                >
                    Place Order
                </button>
            </div>
        </aside>
    </form>
</section>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>