<script lang="ts">
    import Pagination from '$lib/components/Pagination.svelte';
    import { fly, fade } from 'svelte/transition';

    let { data, form } = $props();
</script>

<section class="mx-auto max-w-5xl space-y-10">
    <header class="relative overflow-hidden rounded-[3rem] bg-slate-900 p-10 shadow-2xl shadow-blue-900/10 sm:p-14">
        <div class="relative z-10 flex flex-col gap-3">
            <div class="flex items-center gap-3">
                <span class="h-1.5 w-10 rounded-full bg-blue-500"></span>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-400">Support Hub</p>
            </div>
            <h1 class="text-4xl font-black tracking-tight text-white sm:text-5xl">
                Track your inquiries
            </h1>
            <p class="max-w-md text-sm font-bold leading-relaxed text-slate-400 mt-2 uppercase tracking-wide">
                Manage your product discussions and support tickets in one place.
            </p>
        </div>
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-blue-600/20 blur-[100px]"></div>
    </header>

    {#if form?.error || form?.success}
        <div in:fly={{ y: -10 }} class="overflow-hidden rounded-[2rem] shadow-lg">
            {#if form?.error}
                <p class="bg-rose-50 px-8 py-5 text-sm font-black text-rose-800 ring-1 ring-rose-200 ring-inset">
                    {form.error}
                </p>
            {/if}
            {#if form?.success}
                <p class="bg-emerald-50 px-8 py-5 text-sm font-black text-emerald-800 ring-1 ring-emerald-200 ring-inset">
                    {form.success}
                </p>
            {/if}
        </div>
    {/if}

    <div class="space-y-8">
        {#each data.questions.data as question (question.id)}
            <article class="group overflow-hidden rounded-[3rem] border border-slate-200 bg-white p-2 shadow-sm transition-all duration-500 hover:shadow-2xl hover:shadow-blue-900/5 hover:border-blue-200">
                <div class="p-8 sm:p-12">
                    <div class="mb-10 flex flex-col justify-between gap-6 border-b border-slate-100 pb-10 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-5">
                            <div class="flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-blue-50 text-blue-700 shadow-inner">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-2xl font-black tracking-tight text-slate-900 transition-colors group-hover:text-blue-700">
                                    {question.item?.name ?? 'Deleted Item'}
                                </h2>
                                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-500 mt-1">
                                    {question.created_at ? new Date(question.created_at).toLocaleDateString('th-TH', { 
                                        year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' 
                                    }) : 'Unknown date'}
                                </p>
                            </div>
                        </div>
                        
                        {#if !question.answer_text}
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-5 py-2 text-[10px] font-black uppercase tracking-widest text-amber-700 ring-1 ring-amber-200">
                                <span class="mr-2 h-2 w-2 animate-pulse rounded-full bg-amber-500"></span>
                                Awaiting Staff
                            </span>
                        {:else}
                            <span class="inline-flex items-center rounded-full bg-blue-600 px-5 py-2 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-blue-600/20">
                                Resolved
                            </span>
                        {/if}
                    </div>

                    <div class="grid gap-10 lg:grid-cols-2">
                        <div class="space-y-4">
                            <h4 class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-400 ml-1">Your Inquiry</h4>
                            <div class="rounded-[2rem] bg-slate-50 p-7 ring-1 ring-slate-100">
                                <p class="text-base font-bold leading-relaxed text-slate-900">
                                    {question.question_text}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="text-[10px] font-black uppercase tracking-[0.25em] text-blue-600 ml-1">Staff Response</h4>
                            {#if question.answer_text}
                                <div class="rounded-[2rem] bg-blue-700 p-7 shadow-xl shadow-blue-900/10 ring-1 ring-blue-800">
                                    <div class="mb-3 flex items-center gap-2">
                                        <div class="h-1.5 w-1.5 rounded-full bg-blue-400"></div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-blue-200">
                                            Replied by {question.admin_name}
                                        </p>
                                    </div>
                                    <p class="text-base font-bold leading-relaxed text-white">
                                        {question.answer_text}
                                    </p>
                                </div>
                            {:else}
                                <div class="flex h-full min-h-[120px] items-center justify-center rounded-[2rem] border-2 border-dashed border-slate-200 bg-white/50">
                                    <div class="text-center space-y-2">
                                        <svg class="mx-auto h-6 w-6 text-slate-300 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" stroke-width="2" stroke-linecap="round"/></svg>
                                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Under Review...</p>
                                    </div>
                                </div>
                            {/if}
                        </div>
                    </div>

                    {#if question.answer_text}
                        <div class="mt-12 border-t border-slate-100 pt-8">
                            <details class="group/report">
                                <summary class="flex w-fit cursor-pointer list-none items-center gap-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 transition-colors hover:text-rose-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-open/report:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                    Flag inappropriate response
                                </summary>
                                <form class="mt-6 space-y-5" method="POST" action="?/report" in:fade>
                                    <input name="question_id" type="hidden" value={question.id} />
                                    <label class="block">
                                        <textarea 
                                            class="w-full resize-none rounded-[1.5rem] border-slate-200 bg-white p-6 text-sm font-bold text-slate-900 placeholder:text-slate-400 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10" 
                                            name="reason" 
                                            rows="4" 
                                            placeholder="Briefly explain your concern..."
                                            minlength="10" 
                                            maxlength="255" 
                                            required
                                        ></textarea>
                                    </label>
                                    <button class="rounded-2xl bg-slate-900 px-8 py-3.5 text-[11px] font-black uppercase tracking-widest text-white shadow-lg transition-all hover:bg-rose-600 hover:-translate-y-1 active:scale-95" type="submit">
                                        Submit Report
                                    </button>
                                </form>
                            </details>
                        </div>
                    {/if}
                </div>
            </article>
        {/each}
    </div>

    <footer class="flex justify-center py-12 border-t border-slate-200">
        <Pagination basePath="/questions" meta={data.questions.meta} />
    </footer>
</section>