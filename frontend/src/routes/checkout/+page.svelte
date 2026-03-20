<script lang="ts">
	import type { Address, CartItem } from '$lib/types';
	let { data, form } = $props<{ data: { cart: CartItem[], addresses: Address[] }, form: { error?: string; success?: string } }>();
	let selectedAddressId = $state('__init__');

	$effect(() => {
		if (selectedAddressId === '__init__') {
			selectedAddressId = data.addresses.find((addr: Address) => addr.is_default)?.id?.toString() ?? '';
		}
	});
</script>

<section class="space-y-6">
	<h1 class="text-3xl font-semibold">Checkout</h1>

	{#if form?.error}
		<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
	{/if}
	{#if form?.success}
		<p class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{form.success}</p>
	{/if}

	{#if data.cart.length === 0}
		<p class="text-slate-500">Your cart is empty.</p>
	{:else}
		<form class="space-y-4" method="POST" action="?/placeOrder">
			<div class="panel space-y-4">
				<h2 class="text-xl font-semibold">Shipping Address</h2>

				{#if data.addresses.length > 0}
					<select bind:value={selectedAddressId} class="w-full rounded-2xl border-slate-300" name="address_id">
						<option value="">Enter a new address</option>
						{#each data.addresses as addr (addr.id)}
							<option value={addr.id.toString()}>{addr.label} · {addr.formatted}</option>
						{/each}
					</select>
				{/if}

				{#if data.addresses.length === 0 || selectedAddressId === ''}
					<div class="subtle-box grid gap-3 p-4">
						<input class="rounded-2xl border-slate-300" name="label" placeholder="Home, Dorm, Office" />
						<input class="rounded-2xl border-slate-300" name="recipient_name" placeholder="Recipient name" />
						<input class="rounded-2xl border-slate-300" name="phone" placeholder="Phone" />
						<input class="rounded-2xl border-slate-300" name="country" placeholder="Country" value="Thailand" />
						<input class="rounded-2xl border-slate-300" name="line_1" placeholder="Address line 1" />
						<input class="rounded-2xl border-slate-300" name="line_2" placeholder="Address line 2" />
						<input class="rounded-2xl border-slate-300" name="district" placeholder="District" />
						<input class="rounded-2xl border-slate-300" name="province" placeholder="Province" />
						<input class="rounded-2xl border-slate-300" name="postal_code" placeholder="Postal code" />
					</div>
				{/if}
			</div>

			<div class="panel space-y-4">
				<h2 class="text-xl font-semibold">Items</h2>
				{#each data.cart as item (item.id)}
					<div class="flex justify-between border-b border-slate-200 py-2">
						<span>{item.name} x {item.quantity}</span>
						<span>฿{(item.price * item.quantity).toFixed(2)}</span>
						<input type="hidden" name="cart_item_id" value={item.id} />
					</div>
				{/each}
			</div>

			<button class="btn-primary w-full" type="submit">Place Order</button>
		</form>
	{/if}
</section>