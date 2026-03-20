<script lang="ts">
	import { user } from '$lib/stores/auth';
	import { cart } from '$lib/stores/cart';
	import { derived } from 'svelte/store';

	const cartCount = derived(cart, $c => $c.reduce((sum, i) => sum + i.quantity, 0));
</script>

<nav class="flex justify-between p-4 bg-gray-900 text-white">
	<div class="flex gap-4">
		<a href="/">Home</a>
		<a href="/shop">Shop</a>
	</div>

	<div class="flex gap-4 items-center">
		<a href="/cart">Cart ({$cartCount})</a>

		{#if $user}
			<span>{$user.name}</span>
			<a href="/orders">Orders</a>
			<a href="/logout">Logout</a>
		{:else}
			<a href="/login">Login</a>
		{/if}
	</div>
</nav>