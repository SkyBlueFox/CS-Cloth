<script lang="ts">
    import { itemImageSrc } from '$lib/media';
    import { fly, fade } from 'svelte/transition';
    let { data, form } = $props();
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

<section class="grid gap-8 lg:grid-cols-[1.2fr_1fr] xl:gap-12">
    
    <div class="space-y-8">
        
        <article class="relative overflow-hidden rounded-[2.5rem] border border-slate-100 bg-white p-6 shadow-sm sm:p-8">
            {#if itemImageSrc(data.item)}
                <div class="mb-8 overflow-hidden rounded-[2rem] bg-slate-50">
                    <img
                        class="h-[32rem] w-full object-cover transition-transform duration-700 hover:scale-105"
                        src={itemImageSrc(data.item) ?? undefined}
                        alt={data.item.name}
                    />
                </div>
            {:else}
                <div class="mb-8 flex h-[32rem] w-full flex-col items-center justify-center gap-3 rounded-[2rem] bg-slate-50 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xs font-bold uppercase tracking-widest opacity-40">No Image Available</span>
                </div>
            {/if}
            
            <div class="flex items-center gap-2">
                <span class="h-1.5 w-8 rounded-full bg-blue-600"></span>
                <p class="text-[10px] font-bold uppercase tracking-widest text-blue-600">Product Details</p>
            </div>
            
            <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">{data.item.name}</h1>
            <p class="mt-4 text-base leading-relaxed text-slate-500">{data.item.description}</p>
            
            <div class="mt-8 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-6">
                <div class="flex items-baseline gap-1">
                    <span class="text-sm font-semibold text-slate-400">฿</span>
                    <span class="text-4xl font-black tracking-tight text-blue-700">{data.item.price.toLocaleString(undefined, { minimumFractionDigits: 2 })}</span>
                </div>
                <div class="h-8 w-px bg-slate-200"></div>
                <span class="rounded-full bg-blue-50 px-4 py-1.5 text-xs font-bold text-blue-700">
                    {data.item.stock} in stock
                </span>
            </div>
        </article>

        <section class="rounded-[2.5rem] border border-slate-100 bg-white p-6 shadow-sm sm:p-8">
            <header class="mb-6">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Questions</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-900">Customer Discussion</h2>
            </header>

            {#if data.questions.length === 0}
                <div class="flex flex-col items-center justify-center rounded-[2rem] border border-dashed border-slate-200 bg-slate-50 py-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mb-3 h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <p class="text-sm font-medium text-slate-500">No questions yet. Be the first to ask!</p>
                </div>
            {:else}
                <div class="space-y-6">
                    {#each data.questions as question (question.id)}
                        <article class="overflow-hidden rounded-[2rem] border border-slate-100 bg-slate-50/50 p-5 transition-colors hover:bg-slate-50">
                            <div class="flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-600">
                                    {question.asker_name.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-slate-700">{question.asker_name} <span class="font-normal text-slate-400">asked</span></p>
                                    <p class="mt-1 text-sm text-slate-900">{question.question_text}</p>
                                </div>
                            </div>

                            {#if question.answer_text}
                                <div class="mt-5 ml-11 rounded-[1.5rem] bg-white p-4 shadow-sm ring-1 ring-slate-100">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700">A</div>
                                            <p class="text-xs font-semibold text-blue-700">{question.admin_name ?? 'Admin'} <span class="font-normal text-slate-400">answered</span></p>
                                        </div>
                                        <button
                                            aria-label={question.is_reported_by_current_user ? 'Already reported' : 'Report answer'}
                                            class={`flex h-8 w-8 shrink-0 items-center justify-center rounded-full border transition-all ${
                                                question.is_reported_by_current_user
                                                    ? 'cursor-not-allowed border-transparent bg-slate-100 text-slate-300'
                                                    : 'border-slate-200 bg-white text-slate-400 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600'
                                            }`}
                                            disabled={question.is_reported_by_current_user}
                                            title={question.is_reported_by_current_user ? "You've already flagged this reply" : 'Report this answer'}
                                            type="button"
                                            onclick={() => !question.is_reported_by_current_user && openReport(question.id)}
                                        >
                                            <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M5 4v16" />
                                                <path d="M5 5h10l-2 4 2 4H5" />
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="mt-2 text-sm text-slate-700">{question.answer_text}</p>
                                </div>
                            {:else}
                                <div class="mt-4 ml-11 flex items-center gap-2">
                                    <span class="flex h-2 w-2 rounded-full bg-amber-400"></span>
                                    <p class="text-xs font-medium text-amber-700">Pending admin answer...</p>
                                </div>
                            {/if}
                        </article>
                    {/each}
                </div>
            {/if}
        </section>
    </div>

    <div class="space-y-6">
        
        {#if form?.error}
            <div class="flex items-center gap-3 rounded-2xl bg-rose-50 px-5 py-4 text-sm font-medium text-rose-700 ring-1 ring-rose-200" in:fly={{ y: -10, duration: 300 }}>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                {form.error}
            </div>
        {/if}
        {#if form?.success}
            <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200" in:fly={{ y: -10, duration: 300 }}>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                {form.success}
            </div>
        {/if}

        <form class="sticky top-24 rounded-[2.5rem] border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8" method="POST" action="?/order">
            <header class="mb-6">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Checkout</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-900">Place an order</h2>
            </header>

            <div class="space-y-5">
                <label class="block group">
                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500 transition-colors group-focus-within:text-blue-600">Quantity</span>
                    <input 
                        class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10" 
                        name="quantity" 
                        type="number" 
                        min="1" 
                        max={data.item.stock} 
                        value="1" 
                    />
                </label>

                {#if data.addresses.length > 0}
                    <label class="block group">
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500 transition-colors group-focus-within:text-blue-600">Shipping Address</span>
                        <select 
                            bind:value={selectedAddressId} 
                            class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10" 
                            name="address_id"
                        >
                            <option value="" class="font-semibold text-blue-600">+ Enter a new address</option>
                            {#each data.addresses as address (address.id)}
                                <option value={address.id.toString()}>{address.label} · {address.formatted}</option>
                            {/each}
                        </select>
                    </label>
                {/if}

                {#if data.addresses.length === 0 || selectedAddressId === ''}
                    <div class="rounded-[1.5rem] border border-blue-100 bg-blue-50/50 p-5" in:fade={{ duration: 200 }}>
                        <p class="mb-4 text-xs font-bold uppercase tracking-wider text-blue-800">New Address Details</p>
                        <div class="grid gap-3 md:grid-cols-2">
                            <input class="w-full rounded-xl border-slate-200 px-3 py-2 text-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500/20 md:col-span-2" name="label" placeholder="Address Label (e.g. Home, Office)" required />
                            <input class="w-full rounded-xl border-slate-200 px-3 py-2 text-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500/20" name="recipient_name" placeholder="Recipient Name" required />
                            <input class="w-full rounded-xl border-slate-200 px-3 py-2 text-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500/20" name="phone" placeholder="Phone Number" required />
                            <input class="w-full rounded-xl border-slate-200 px-3 py-2 text-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500/20 md:col-span-2" name="line_1" placeholder="Address Line 1" required />
                            <input class="w-full rounded-xl border-slate-200 px-3 py-2 text-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500/20 md:col-span-2" name="line_2" placeholder="Address Line 2 (Optional)" />
                            <input class="w-full rounded-xl border-slate-200 px-3 py-2 text-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500/20" name="district" placeholder="District" required />
                            <input class="w-full rounded-xl border-slate-200 px-3 py-2 text-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500/20" name="province" placeholder="Province" required />
                            <input class="w-full rounded-xl border-slate-200 px-3 py-2 text-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500/20" name="postal_code" placeholder="Postal Code" required />
                            <input class="w-full rounded-xl border-slate-200 px-3 py-2 text-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500/20" name="country" placeholder="Country" value="Thailand" required />
                        </div>
                        <div class="mt-4 space-y-2">
                            <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer hover:text-slate-900">
                                <input name="save_address" type="checkbox" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                Save this address for future orders
                            </label>
                            <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer hover:text-slate-900">
                                <input name="set_as_default" type="checkbox" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                Set as default address
                            </label>
                        </div>
                    </div>
                {/if}

                <button class="mt-2 w-full rounded-2xl bg-blue-600 px-4 py-4 text-sm font-bold text-white shadow-lg shadow-blue-600/25 transition-all hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-blue-700/30 active:translate-y-0" type="submit">
                    Complete Order
                </button>
            </div>
        </form>

        <form class="rounded-[2.5rem] border border-slate-100 bg-white p-6 shadow-sm sm:p-8" method="POST" action="?/question">
            <header class="mb-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Support</p>
                <h2 class="mt-1 text-xl font-bold text-slate-900">Have a question?</h2>
            </header>
            <div class="space-y-4">
                <textarea 
                    class="w-full resize-none rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm placeholder:text-slate-400 transition-all focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100" 
                    name="question_text" 
                    rows="3" 
                    maxlength="255" 
                    placeholder="Ask about details, sizing, or stock..."
                    required
                ></textarea>
                <button class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition-colors hover:bg-slate-50 active:bg-slate-100" type="submit">
                    Send to Admin
                </button>
            </div>
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
            class="absolute inset-0 h-full w-full cursor-default bg-slate-900/40 backdrop-blur-sm transition-opacity"
            type="button"
            onclick={closeReport}
        ></button>
        
        <form 
            class="relative w-full max-w-lg overflow-hidden rounded-[2.5rem] bg-white p-8 shadow-2xl ring-1 ring-slate-900/5" 
            method="POST" 
            action="?/report"
            in:fly={{ y: 20, duration: 300, delay: 50 }}
        >
            <input name="question_id" type="hidden" value={reportQuestionId} />
            
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-rose-100 text-rose-600 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900">Report Answer</h3>
                    <p class="mt-2 text-sm text-slate-500">Please explain why this answer is inappropriate or unhelpful. Our team will review it shortly.</p>
                </div>
                <button 
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition-colors hover:bg-slate-200 hover:text-slate-900" 
                    type="button" 
                    onclick={closeReport}
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <label class="block">
                <span class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Reason</span>
                <textarea
                    bind:value={reportReason}
                    class="w-full resize-none rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all focus:border-rose-400 focus:bg-white focus:ring-4 focus:ring-rose-400/10"
                    name="reason"
                    rows="4"
                    minlength="10"
                    maxlength="255"
                    placeholder="Enter details here (minimum 10 characters)..."
                    required
                ></textarea>
            </label>
            
            <div class="mt-8 flex flex-col-reverse justify-end gap-3 sm:flex-row">
                <button class="w-full rounded-xl px-4 py-3 text-sm font-bold text-slate-600 transition-colors hover:bg-slate-100 sm:w-auto" type="button" onclick={closeReport}>
                    Cancel
                </button>
                <button class="w-full rounded-xl bg-rose-600 px-6 py-3 text-sm font-bold text-white shadow-md shadow-rose-600/20 transition-all hover:bg-rose-700 active:scale-95 sm:w-auto" type="submit">
                    Submit Report
                </button>
            </div>
        </form>
    </div>
{/if}