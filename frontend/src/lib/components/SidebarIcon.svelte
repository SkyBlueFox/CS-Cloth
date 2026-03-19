<script lang="ts">
	let { name, active = false } = $props<{ name: string; active?: boolean }>();
</script>

<span class={`sidebar-icon ${active ? 'sidebar-icon-active' : ''}`}>
	<svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 24 24">
		{#if name === 'dashboard'}
			<path d="M4 5h7v6H4z" />
			<path d="M13 5h7v10h-7z" />
			<path d="M4 13h7v6H4z" />
			<path d="M13 17h7v2h-7z" />
		{:else if name === 'shop'}
			<path d="M5 8h14l-1 11H6L5 8Z" />
			<path d="M9 8V6a3 3 0 0 1 6 0v2" />
		{:else if name === 'orders'}
			<path d="M7 4h10" />
			<path d="M6 8h12" />
			<path d="M8 12h8" />
			<path d="M9 16h6" />
			<path d="M5 4h14v16H5z" />
		{:else if name === 'questions'}
			<path d="M7 8a5 5 0 1 1 8 4v2l2 2H7a5 5 0 0 1 0-8Z" />
			<path d="M12 18v2" />
		{:else if name === 'items'}
			<path d="M12 3 4 7l8 4 8-4-8-4Z" />
			<path d="M4 7v10l8 4 8-4V7" />
			<path d="M12 11v10" />
		{:else if name === 'admins'}
			<path d="M9 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
			<path d="M15 13a3 3 0 1 0 0-6" />
			<path d="M4 19a5 5 0 0 1 10 0" />
			<path d="M14 19a5 5 0 0 1 6-4" />
		{:else if name === 'users'}
			<path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
			<path d="M4 20a8 8 0 0 1 16 0" />
		{:else if name === 'reports'}
			<path d="M6 4h9l3 3v13H6z" />
			<path d="M15 4v4h4" />
			<path d="M9 12h6" />
			<path d="M9 16h4" />
		{:else if name === 'profile'}
			<path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
			<path d="M5 20a7 7 0 0 1 14 0" />
		{:else if name === 'logout'}
			<path d="M10 17l5-5-5-5" />
			<path d="M15 12H3" />
			<path d="M12 4h6v16h-6" />
		{:else}
			<path d="M5 12h14" />
			<path d="M12 5v14" />
		{/if}
	</svg>
</span>
