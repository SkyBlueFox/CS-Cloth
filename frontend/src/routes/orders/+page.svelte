<script lang="ts">
	import { itemImageSrc } from '$lib/media';
	import Pagination from '$lib/components/Pagination.svelte';
	import type { Order, PaginationMeta } from '$lib/types';

	interface OrdersData {
		orders: {
			data: Order[];
			meta: PaginationMeta;
		};
		filters: {
			search: string;
		};
	}

	interface FormData {
		error?: string;
		success?: string;
	}

	let { data, form } = $props<{ data: OrdersData; form?: FormData }>();
	const paginationBasePath = $derived.by(() => {
		const params = new URLSearchParams();

		if (data.filters.search) {
			params.set('search', data.filters.search);
		}

		const query = params.toString();
		return query ? `/orders?${query}` : '/orders';
	});

	function formatDate(value: string | null) {
		if (!value) return 'Unknown date';

		return new Intl.DateTimeFormat('en-GB', {
			dateStyle: 'medium',
			timeStyle: 'short'
		}).format(new Date(value));
	}
</script>

<section class="space-y-6">
	<div class="panel">
		<p class="eyebrow">Orders</p>
		<h1 class="mt-2 text-3xl font-semibold">Your purchase history</h1>
		<p class="mt-3 text-sm text-slate-600">Search by item name or order ID to find a past purchase fast.</p>
	</div>

	<form class="panel flex flex-col gap-3 md:flex-row" method="GET" action="/orders">
		<input
			class="w-full rounded-2xl border-slate-300"
			name="search"
			placeholder="Search by item name or order ID"
			type="search"
			value={data.filters.search}
		/>
		<div class="flex gap-3">
			<button class="btn-primary" type="submit">Search</button>
			<a class="btn-secondary" href="/orders">Reset</a>
		</div>
	</form>

	{#if form?.error}
		<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
	{/if}
	{#if form?.success}
		<p class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{form.success}</p>
	{/if}

	{#if data.orders.data.length === 0}
		<div class="panel py-12 text-center">
			<p class="text-lg font-medium text-slate-800">No orders matched your search.</p>
			<p class="mt-2 text-sm text-slate-500">Try a different item name or order ID.</p>
		</div>
	{:else}
		<div class="space-y-5">
			{#each data.orders.data as order (order.id)}
				<article class="panel space-y-5">
					<div class="flex flex-wrap items-start justify-between gap-4">
						<div>
							<a class="text-xl font-semibold text-sky-950 hover:text-blue-800" href={`/orders/${order.id}`}>
								Order ID {order.order_number}
							</a>
							<p class="mt-1 text-sm text-slate-500">
								{order.status} · ฿{order.total_price.toFixed(2)} · Placed {formatDate(order.created_at)}
							</p>
						</div>
						<div class="flex gap-3">
							<a class="btn-secondary rounded-full px-4 py-2 text-sm" href={`/orders/${order.id}`}>View details</a>
							{#if order.status === 'pending'}
								<form method="POST" action="?/cancel">
									<input name="order_id" type="hidden" value={order.id} />
									<button class="rounded-full border border-rose-300 px-4 py-2 text-sm text-rose-700" type="submit">Cancel</button>
								</form>
							{/if}
							{#if order.status === 'shipped' || order.status === 'partially_refunded'}
								<a class="rounded-full border border-amber-300 px-4 py-2 text-sm text-amber-700" href={`/orders/${order.id}`}>
									Request refund
								</a>
							{/if}
						</div>
					</div>
					<p class="text-sm text-slate-600">{order.shipping_address_formatted ?? order.shipping_address}</p>
					<div class="grid gap-3">
						{#each order.items as line (line.id)}
							<div class="rounded-[1.5rem] border border-slate-200 p-4">
								<div class="flex items-center gap-4">
									<a class="shrink-0" href={`/orders/${order.id}`}>
										{#if line.item && itemImageSrc(line.item)}
											<img
												alt={line.item.name}
												class="h-20 w-20 rounded-[1.2rem] object-cover transition hover:scale-[1.03]"
												src={itemImageSrc(line.item) ?? undefined}
											/>
										{:else}
											<div class="subtle-box flex h-20 w-20 items-center justify-center text-xs text-sky-500">No image</div>
										{/if}
									</a>
									<div>
										<a class="font-medium text-sky-950 hover:text-blue-800" href={`/orders/${order.id}`}>
											{line.item?.name ?? `Item #${line.item_id}`}
										</a>
										<p class="mt-1 text-sm text-slate-500">{line.quantity} × ฿{line.price_at_purchase.toFixed(2)}</p>
										{#if line.refund_requested_quantity > 0}
											<p class="mt-1 text-xs font-medium text-amber-700">
												Refund requested for {line.refund_requested_quantity} item(s)
											</p>
										{/if}
										{#if line.refunded_quantity > 0}
											<p class="mt-1 text-xs font-medium text-emerald-700">
												Refund approved for {line.refunded_quantity} item(s)
											</p>
										{/if}
									</div>
								</div>
							</div>
						{/each}
					</div>
				</article>
			{/each}
		</div>
	{/if}

	<Pagination basePath={paginationBasePath} meta={data.orders.meta} />
</section>
