<script lang="ts">
    import type { PaginationMeta } from '$lib/types';

    let { basePath, meta, paramName = 'page' } = $props<{ basePath: string; meta: PaginationMeta; paramName?: string }>();

    const urlFor = (page: number): string =>
        `${basePath}${basePath.includes('?') ? '&' : '?'}${paramName}=${page}`;
</script>

{#if meta.last_page > 1}
    <nav class="mt-10 flex items-center justify-center gap-2">
        {#if meta.current_page > 1}
            <a 
                class="group flex h-10 items-center gap-2 rounded-full px-4 text-sm font-medium text-slate-500 transition-all hover:bg-slate-50 hover:text-slate-900 active:scale-95" 
                href={urlFor(meta.current_page - 1)}
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Prev
            </a>
        {/if}

        <div class="flex items-center gap-1 rounded-full bg-slate-50/50 px-4 py-2 ring-1 ring-slate-100">
            <span class="text-sm font-semibold text-slate-900">{meta.current_page}</span>
            <span class="text-sm text-slate-400">/</span>
            <span class="text-sm text-slate-500">{meta.last_page}</span>
        </div>

        {#if meta.current_page < meta.last_page}
            <a 
                class="group flex h-10 items-center gap-2 rounded-full px-4 text-sm font-medium text-slate-500 transition-all hover:bg-slate-50 hover:text-slate-900 active:scale-95" 
                href={urlFor(meta.current_page + 1)}
            >
                Next
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        {/if}
    </nav>
{/if}