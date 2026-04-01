<script lang="ts">
    import { itemImageSrc } from '$lib/media';
    import { fly, fade } from 'svelte/transition';
    let { data, form = $bindable() } = $props();
    let selectedAddressId = $state('__init__');
    let reportQuestionId = $state<number | null>(null);
    let reportReason = $state('');

    $effect(() => {
        if (selectedAddressId === '__init__') {
            selectedAddressId = data.addresses.find((address) => address.is_default)?.id?.toString() ?? '';
        }
    });

    function openReport(questionId: number) {
        reportQuestionId = questionId;
        reportReason = '';
    }

    function closeReport() {
        reportQuestionId = null;
        reportReason = '';
    }
</script>

<section class="grid gap-12 lg:grid-cols-[1.2fr_1fr] xl:gap-16">
    
    <div class="space-y-12">
        <article class="relative overflow-hidden rounded-[3rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-12">
            {#if itemImageSrc(data.item)}
                <div class="mb-12 overflow-hidden rounded-[2.5rem] bg-slate-50 shadow-inner">
                    <img
                        class="h-[36rem] w-full object-cover transition-transform duration-1000 hover:scale-105"
                        src={itemImageSrc(data.item) ?? undefined}
                        alt={data.item.name}
                    />
                </div>
            {/if}
            
            <div class="flex items-center gap-3">
                <span class="h-1.5 w-10 rounded-full bg-blue-600"></span>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600">Premium Product</p>
            </div>
            
            <h1 class="mt-6 text-4xl font-black tracking-tight text-slate-900 sm:text-6xl">{data.item.name}</h1>
            <p class="mt-6 text-lg font-bold leading-relaxed text-slate-700 uppercase tracking-tight">{data.item.description}</p>
            
            <div class="mt-10 flex flex-wrap items-center gap-6 border-t border-slate-50 pt-10">
                <div class="flex items-baseline gap-2">
                    <span class="text-sm font-black text-slate-400">฿</span>
                    <span class="text-5xl font-black tracking-tighter text-slate-900">
                        {data.item.price.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                    </span>
                </div>
                <div class="h-10 w-px bg-slate-200"></div>
                <span class="rounded-full bg-blue-50 px-6 py-2 text-xs font-black uppercase tracking-widest text-blue-700 ring-1 ring-blue-100">
                    {data.item.stock} Available in stock
                </span>
            </div>
        </article>

        {#if form?.error || form?.success}
            <div in:fly={{ y: -10 }} class="overflow-hidden rounded-[2rem] shadow-lg">
                {#if form?.error}
                    <p class="bg-rose-50 px-8 py-5 text-sm font-black text-rose-800 ring-1 ring-rose-200 ring-inset">
                        {form.error}
                    </p>
                {:else if form?.success}
                    <p class="bg-emerald-50 px-8 py-5 text-sm font-black text-emerald-800 ring-1 ring-emerald-200 ring-inset">
                        {form.success}
                    </p>
                {/if}
            </div>
        {/if}

        <section class="rounded-[3rem] border border-slate-100 bg-white p-8 shadow-sm sm:p-12">
            <header class="mb-10 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Q&A Archive</p>
                    <h2 class="mt-2 text-3xl font-black text-slate-900">Discussion</h2>
                </div>
            </header>

            {#if data.questions.length === 0}
                <div class="flex flex-col items-center justify-center rounded-[2.5rem] border-2 border-dashed border-slate-100 bg-slate-50 py-16 text-center">
                    <p class="text-sm font-black uppercase tracking-widest text-slate-400">No active discussions</p>
                </div>
            {:else}
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
                                <div class="mt-8 ml-10 rounded-[2rem] bg-blue-600 p-7 text-white shadow-xl shadow-blue-600/20 ring-1 ring-blue-700">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-1.5 w-1.5 rounded-full bg-blue-300"></div>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-blue-100">Staff Response by {question.admin_name}</p>
                                        </div>
                                        <button 
                                            class="text-white/50 hover:text-white transition-colors" 
                                            onclick={() => !question.is_reported_by_current_user && openReport(question.id)}
                                            title="Report Answer"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 4v16M5 5h10l-2 4 2 4H5" /></svg>
                                        </button>
                                    </div>
                                    <p class="text-sm font-black leading-relaxed">{question.answer_text}</p>
                                </div>
                            {:else}
                                <div class="mt-6 ml-10 flex items-center gap-3">
                                    <span class="h-2 w-2 animate-ping rounded-full bg-amber-400"></span>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-600">Reviewing In Progress</p>
                                </div>
                            {/if}
                        </article>
                    {/each}
                </div>
            {/if}
        </section>
    </div>

    <div class="space-y-8">
        <form class="sticky top-24 rounded-[3rem] bg-slate-900 p-10 text-white shadow-2xl" method="POST" action="?/order">
            <header class="mb-10 border-b border-white/10 pb-8">
                <p class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-500">Fast Checkout</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight">Order Now</h2>
            </header>

            <div class="space-y-8">
                <label class="block group">
                    <span class="mb-3 block text-[10px] font-black uppercase tracking-widest text-slate-500 group-focus-within:text-blue-400 transition-colors">Order Quantity</span>
                    <input class="w-full rounded-2xl border-none bg-white/10 px-6 py-4 text-lg font-black text-white outline-none focus:ring-4 focus:ring-blue-600/50" name="quantity" type="number" min="1" max={data.item.stock} value="1" />
                </label>

                <label class="block group">
                    <span class="mb-3 block text-[10px] font-black uppercase tracking-widest text-slate-500 group-focus-within:text-blue-400 transition-colors">Ship to</span>
                    <select class="w-full rounded-2xl border-none bg-white/10 px-6 py-4 text-sm font-bold text-white outline-none focus:ring-4 focus:ring-blue-600/50" name="address_id" bind:value={selectedAddressId}>
                        {#each data.addresses as address (address.id)}
                            <option value={address.id.toString()} class="text-slate-900">{address.label} · {address.province}</option>
                        {/each}
                        <option value="" class="text-blue-500 font-bold">+ Use New Address</option>
                    </select>
                </label>

                <button class="w-full rounded-2xl bg-blue-600 py-5 text-sm font-black uppercase tracking-[0.3em] shadow-xl shadow-blue-600/30 transition-all hover:bg-blue-500 hover:-translate-y-1 active:translate-y-0" type="submit">
                    Complete Purchase
                </button>
            </div>
        </form>

        <form class="rounded-[2.5rem] bg-white p-8 shadow-xl shadow-slate-200/50 ring-1 ring-slate-100" method="POST" action="?/question">
            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Got Questions?</h3>
            <textarea class="mt-6 w-full resize-none rounded-2xl border-slate-100 bg-slate-50 p-5 text-sm font-bold text-slate-900 placeholder:text-slate-300 focus:border-blue-500 focus:ring-0" name="question_text" rows="3" placeholder="Inquire about sizing, materials or restock..."></textarea>
            <button class="mt-4 w-full rounded-xl py-3 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-blue-600 transition-colors">Send Inquiry</button>
        </form>
    </div>
</section>

{#if reportQuestionId !== null}
    <div 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        in:fade={{ duration: 200 }}
        out:fade={{ duration: 150 }}
    >
        <button
            aria-label="Close dialog"
            class="absolute inset-0 h-full w-full cursor-default bg-slate-900/60 backdrop-blur-md transition-opacity"
            type="button"
            onclick={closeReport}
        ></button>
        
        <form 
            class="relative w-full max-w-lg overflow-hidden rounded-[3rem] bg-white p-10 shadow-2xl ring-1 ring-slate-900/5" 
            method="POST" 
            action="?/report"
            in:fly={{ y: 20, duration: 300 }}
        >
            <input name="question_id" type="hidden" value={reportQuestionId} />
            
            <div class="mb-8">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h3 class="text-3xl font-black text-slate-900 tracking-tight">Report Answer</h3>
                <p class="mt-3 text-sm font-bold text-slate-500 uppercase tracking-wide">Tell us why this response is inappropriate.</p>
            </div>
            
            <label class="block">
                <textarea
                    bind:value={reportReason}
                    class="w-full resize-none rounded-2xl border-slate-200 bg-slate-50 p-6 text-sm font-black text-slate-900 transition-all focus:border-rose-400 focus:bg-white focus:ring-4 focus:ring-rose-400/10"
                    name="reason"
                    rows="4"
                    minlength="10"
                    maxlength="255"
                    placeholder="Provide specific details here..."
                    required
                ></textarea>
            </label>
            
            <div class="mt-10 flex flex-col-reverse justify-end gap-4 sm:flex-row">
                <button class="w-full rounded-2xl px-6 py-4 text-xs font-black uppercase tracking-widest text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-900 sm:w-auto" type="button" onclick={closeReport}>
                    Cancel
                </button>
                <button class="w-full rounded-2xl bg-rose-600 px-8 py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-rose-600/20 transition-all hover:bg-rose-700 active:scale-95 sm:w-auto" type="submit">
                    Send Report
                </button>
            </div>
        </form>
    </div>
{/if}