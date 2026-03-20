<script lang="ts">
	import type { PaginationMeta } from '$lib/types';

	let { basePath, meta, paramName = 'page' } = $props<{ basePath: string; meta: PaginationMeta; paramName?: string }>();

	const urlFor = (page: number): string =>
		`${basePath}${basePath.includes('?') ? '&' : '?'}${paramName}=${page}`;
</script>

{#if meta.last_page > 1}
	<nav class="mt-6 flex items-center gap-3">
		{#if meta.current_page > 1}
			<a class="btn-secondary rounded-full px-4 py-2 text-sm" href={urlFor(meta.current_page - 1)} >
				Previous
			</a>
		{/if}

		<span class="text-sm text-sky-800">
			Page {meta.current_page} of {meta.last_page}
		</span>

		{#if meta.current_page < meta.last_page}
			<a class="btn-secondary rounded-full px-4 py-2 text-sm" href={urlFor(meta.current_page + 1)}>
				Next
			</a>
		{/if}
	</nav>
{/if}