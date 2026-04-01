<script lang="ts">
	import { itemImageSrc } from '$lib/media';
	let { data, form } = $props();
</script>

<section class="space-y-6">
	<h1 class="text-3xl font-semibold">My Cart</h1>

	{#if form?.error}
		<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
	{/if}
	{#if form?.success}
		<p class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{form.success}</p>
	{/if}

	{#if data.cart?.length === 0}
		<p class="text-slate-500">Your cart is empty.</p>
	{:else}
		<div class="space-y-4">
			{#each data.cart as item (item.id)}
				<div class="panel flex justify-between items-center gap-4">
					<div class="flex gap-4">
						<img class="h-24 w-24 rounded-xl object-cover" src={itemImageSrc(item) ?? undefined} alt={item.name} />
						<div>
							<h2 class="font-semibold">{item.name}</h2>
							<p class="text-sm text-slate-500">฿{item.price.toFixed(2)}</p>
						</div>
					</div>
					<form method="POST" action="?/update" class="flex items-center gap-2">
						<input type="hidden" name="item_id" value={item.id} />
						<input type="number" name="quantity" min="1" class="w-16 rounded-2xl border px-2 py-1" value={item.quantity} />
						<button type="submit" class="btn-secondary">Update</button>
					</form>
					<form method="POST" action="?/remove">
						<input type="hidden" name="item_id" value={item.id} />
						<button type="submit" class="btn-danger">Remove</button>
					</form>
				</div>
			{/each}
		</div>
		<a href="/checkout" class="btn-primary mt-4 inline-block">Proceed to Checkout</a>
	{/if}
</section>
