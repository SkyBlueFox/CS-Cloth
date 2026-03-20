<script lang="ts">
	let { name, active = false } = $props<{ name: IconName; active?: boolean }>();

	export type IconName =
		| 'dashboard'
		| 'shop'
		| 'orders'
		| 'questions'
		| 'items'
		| 'admins'
		| 'users'
		| 'reports'
		| 'profile'
		| 'logout';
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
		{/if}
	</svg>
</span>