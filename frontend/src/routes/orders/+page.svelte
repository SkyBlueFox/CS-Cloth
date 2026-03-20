<script lang="ts">
	import Pagination from '$lib/components/Pagination.svelte';
	import type { Order, PaginationMeta } from '$lib/types';

	interface OrdersData {
		orders: {
			data: Order[];
			meta: PaginationMeta;
		};
	}

	interface FormData {
		error?: string;
		success?: string;
	}

	let { data, form } = $props<{ data: OrdersData; form?: FormData }>();
</script>

<section class="space-y-6">
	<div class="panel">
		<p class="eyebrow">Orders</p>
		<h1 class="mt-2 text-3xl font-semibold">Your purchase history</h1>
	</div>

	{#if form?.error}
		<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
	{/if}
	{#if form?.success}
		<p class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{form.success}</p>
	{/if}

	<div class="space-y-5">
		{#each data.orders.data as order (order.id)}
			<article class="panel space-y-4">
				<div class="flex flex-wrap items-center justify-between gap-4">
					<div>
						<h2 class="text-xl font-semibold">Order #{order.id}</h2>
						<p class="text-sm text-slate-500">{order.status} · ฿{order.total_price.toFixed(2)}</p>
					</div>
					<div class="flex gap-3">
						{#if order.status === 'pending'}
							<form method="POST" action="?/cancel">
								<input name="order_id" type="hidden" value={order.id} />
								<button class="rounded-full border border-rose-300 px-4 py-2 text-sm text-rose-700" type="submit">Cancel</button>
							</form>
						{/if}
						{#if order.status === 'shipped'}
							<form method="POST" action="?/refund">
								<input name="order_id" type="hidden" value={order.id} />
								<button class="rounded-full border border-amber-300 px-4 py-2 text-sm text-amber-700" type="submit">Request refund</button>
							</form>
						{/if}
					</div>
				</div>
				<p class="text-sm text-slate-600">{order.shipping_address_formatted ?? order.shipping_address}</p>
				<div class="grid gap-3">
					{#each order.items as line (line.id)}
						<div class="rounded-[1.25rem] border border-slate-200 p-4">
							<p class="font-medium">{line.item?.name ?? `Item #${line.item_id}`}</p>
							<p class="text-sm text-slate-500">{line.quantity} × ฿{line.price_at_purchase.toFixed(2)}</p>
						</div>
					{/each}
				</div>
			</article>
		{/each}
	</div>

	<Pagination basePath="/orders" meta={data.orders.meta} />
</section>