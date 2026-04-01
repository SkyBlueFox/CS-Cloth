<script lang="ts">
    import { itemImageSrc } from '$lib/media';
    import { fly } from 'svelte/transition';
    import { enhance } from '$app/forms';
    import { goto } from '$app/navigation';

    let { data, form = $bindable() } = $props();
    
    // States สำหรับจัดการจำนวนสินค้าในหน้าปัจจุบัน
    let quantity = $state(1); 

    // Logic: ควบคุมจำนวนสินค้าไม่ให้เกิน Stock
    function updateQuantity(val: number) {
        const newQty = quantity + val;
        if (newQty >= 1 && newQty <= data.item.stock) {
            quantity = newQty;
        }
    }

    // ฟังก์ชันสำหรับ "ซื้อเลย" -> เปลี่ยนหน้าไปที่ Checkout พร้อม parameter
    function handleBuyNow() {
        goto(`/checkout?item_id=${data.item.id}&quantity=${quantity}`);
    }
</script>

<section class="mx-auto max-w-4xl space-y-12">
    <div class="space-y-12">
        <article class="relative overflow-hidden rounded-[3rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-12">
            {#if itemImageSrc(data.item)}
                <div class="mb-12 overflow-hidden rounded-[2.5rem] bg-slate-50 shadow-inner">
                    <img class="h-[36rem] w-full object-cover transition-transform duration-1000 hover:scale-105" src={itemImageSrc(data.item)} alt={data.item.name} />
                </div>
            {/if}

            <div class="flex items-center gap-3">
                <span class="h-1.5 w-10 rounded-full bg-blue-600"></span>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600">Premium Product</p>
            </div>

            <h1 class="mt-6 text-4xl font-black tracking-tight text-slate-900 sm:text-6xl uppercase tracking-tighter">{data.item.name}</h1>
            <p class="mt-6 text-lg font-bold uppercase tracking-tight leading-relaxed text-slate-700">{data.item.description}</p>

            <div class="mt-10 flex flex-wrap items-center gap-6 border-t border-slate-50 pt-10">
                <div class="flex items-baseline gap-2">
                    <span class="text-sm font-black text-slate-400">฿</span>
                    <span class="text-5xl font-black tracking-tighter text-slate-900">{data.item.price.toLocaleString(undefined, { minimumFractionDigits: 2 })}</span>
                </div>
                <div class="h-10 w-px bg-slate-200"></div>
                <span class="rounded-full bg-blue-50 px-6 py-2 text-xs font-black uppercase tracking-widest text-blue-700 ring-1 ring-blue-100">
                    {data.item.stock} Available in stock
                </span>
            </div>
        </article>

        {#if data.viewerRole === 'user'}
            <div class="panel group space-y-8 p-10 ring-2 ring-blue-600/5 transition-all hover:ring-blue-600/20 bg-white rounded-[3rem]">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                    <div class="space-y-1">
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600">Purchase options</p>
                        <h3 class="text-2xl font-black text-slate-900 uppercase">Select Quantity</h3>
                    </div>

                    <div class="flex items-center gap-4 bg-slate-100 p-1.5 rounded-2xl ring-1 ring-slate-200">
                        <button type="button" class="flex h-12 w-12 items-center justify-center rounded-xl bg-white font-black text-slate-900 shadow-sm hover:bg-slate-50 disabled:opacity-30" onclick={() => updateQuantity(-1)} disabled={quantity <= 1}>−</button>
                        <span class="w-10 text-center font-black text-xl">{quantity}</span>
                        <button type="button" class="flex h-12 w-12 items-center justify-center rounded-xl bg-white font-black text-slate-900 shadow-sm hover:bg-slate-50 disabled:opacity-30" onclick={() => updateQuantity(1)} disabled={quantity >= data.item.stock}>+</button>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <form method="POST" action="?/addToCart" use:enhance>
                        <input type="hidden" name="quantity" value={quantity} />
                        <button type="submit" class="flex w-full items-center justify-center gap-3 rounded-2xl border-2 border-slate-900 py-5 text-sm font-black uppercase tracking-[0.2em] text-slate-900 transition-all hover:bg-slate-900 hover:text-white" disabled={data.item.stock <= 0}>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                            Add to Bag
                        </button>
                    </form>

                    <button 
                        type="button" 
                        onclick={handleBuyNow} 
                        class="w-full rounded-2xl bg-blue-600 py-5 text-sm font-black uppercase tracking-[0.2em] text-white shadow-lg shadow-blue-600/20 transition-all hover:bg-blue-700 active:scale-[0.98]"
                        disabled={data.item.stock <= 0}
                    >
                        Buy it now
                    </button>
                </div>
            </div>
        {:else if !data.viewerRole}
            <div class="panel p-10 text-center space-y-6 rounded-[3rem]">
                <p class="font-bold text-slate-500 uppercase tracking-widest text-sm">Please sign in to purchase this item</p>
                <a href="/login" class="btn-primary inline-block px-12">Login to Shop</a>
            </div>
        {/if}

        {#if form?.error || form?.success}
            <div in:fly={{ y: -10 }} class="overflow-hidden rounded-[2rem] shadow-lg">
                {#if form?.error} 
                    <p class="bg-rose-50 px-8 py-5 text-sm font-black text-rose-800 ring-1 ring-rose-200 ring-inset uppercase tracking-widest text-center">{form.error}</p> 
                {:else if form?.success} 
                    <p class="bg-emerald-50 px-8 py-5 text-sm font-black text-emerald-800 ring-1 ring-emerald-200 ring-inset uppercase tracking-widest text-center">{form.success}</p> 
                {/if}
            </div>
        {/if}

        <section class="rounded-[3rem] border border-slate-100 bg-white p-8 shadow-sm sm:p-12 space-y-10">
            <header class="flex items-center justify-between border-b border-slate-50 pb-8">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Q&A Archive</p>
                    <h2 class="mt-2 text-3xl font-black text-slate-900 uppercase tracking-tight">Discussion</h2>
                </div>
            </header>

            {#if data.viewerRole === 'user'}
                <form 
                    method="POST" 
                    action="?/question" 
                    use:enhance 
                    class="rounded-[2.5rem] border border-slate-200 bg-slate-50 p-8 shadow-inner transition-all focus-within:ring-4 focus-within:ring-blue-600/5 focus-within:border-blue-200"
                >
                    <div class="flex items-center gap-3 mb-5">
                        <span class="h-1.5 w-8 rounded-full bg-blue-600"></span>
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600">Ask the shop</p>
                    </div>
                    
                    <textarea 
                        name="question_text" 
                        rows="3" 
                        placeholder="Ask about sizing, material, or anything else before you buy..."
                        class="w-full resize-none rounded-2xl border-slate-200 bg-white p-5 text-sm font-bold text-slate-900 placeholder:text-slate-300 focus:border-blue-500 focus:ring-0 transition-all shadow-sm"
                        required
                    ></textarea>

                    <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-relaxed max-w-xs text-center sm:text-left">
                            Your question will be visible to everyone once an admin responds.
                        </p>
                        <button 
                            type="submit" 
                            class="w-full sm:w-auto rounded-xl bg-slate-900 px-10 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-white hover:bg-blue-600 transition-all shadow-lg shadow-slate-900/10"
                        >
                            Send Question
                        </button>
                    </div>
                </form>
            {/if}

            <div class="space-y-8">
                {#each data.questions as question (question.id)}
                    <article class="relative overflow-hidden rounded-[2.5rem] bg-slate-50/50 p-8 ring-1 ring-slate-100 transition-all hover:bg-slate-50">
                        <div class="flex items-start gap-5">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white font-black text-slate-900 shadow-sm ring-1 ring-slate-200">
                                {question.asker_name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{question.asker_name} asked:</p>
                                <p class="mt-2 text-base font-bold leading-relaxed text-slate-900">{question.question_text}</p>
                            </div>
                        </div>
                        {#if question.answer_text}
                            <div class="mt-8 ml-10 rounded-[2rem] bg-blue-600 p-7 text-white shadow-xl shadow-blue-600/10">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="h-1.5 w-1.5 rounded-full bg-blue-200"></div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-blue-200">Staff Response</p>
                                </div>
                                <p class="text-sm font-black leading-relaxed">{question.answer_text}</p>
                            </div>
                        {:else}
                            <div class="mt-6 ml-10 flex items-center gap-3">
                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-amber-400"></span>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-600">Pending review</p>
                            </div>
                        {/if}
                    </article>
                {:else}
                    <div class="py-12 text-center border-2 border-dashed border-slate-100 rounded-[2.5rem]">
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">No discussions yet</p>
                    </div>
                {/each}
            </div>
        </section>
    </div>
</section>