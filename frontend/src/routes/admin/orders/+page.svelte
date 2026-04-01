<script lang="ts">
	import { itemImageSrc, storagePathSrc } from '$lib/media';
	import Pagination from '$lib/components/Pagination.svelte';

	let { data, form } = $props();
	let zoomedEvidenceImage = $state<string | null>(null);
	const refundReasons = [
		{ value: 'damaged_item', label: 'Item arrived damaged' },
		{ value: 'wrong_item', label: 'Wrong item received' },
		{ value: 'missing_parts', label: 'Missing parts or accessories' },
		{ value: 'not_as_described', label: 'Item not as described' },
		{ value: 'quality_issue', label: 'Quality issue' },
		{ value: 'changed_mind', label: 'No longer needed' },
		{ value: 'other', label: 'Other' }
	];
	const paginationBasePath = $derived.by(() => {
		const params = new URLSearchParams();

		if (data.filters.search) {
			params.set('search', data.filters.search);
		}

		if (data.filters.sort && data.filters.sort !== 'newest') {
			params.set('sort', data.filters.sort);
		}

		if (data.filters.queue && data.filters.queue !== 'shipping') {
			params.set('queue', data.filters.queue);
		}

		for (const reason of data.filters.refundReasons ?? []) {
			params.append('refund_reasons', reason);
		}

		const query = params.toString();
		return query ? `/admin/orders?${query}` : '/admin/orders';
	});

	function formatDate(value: string | null) {
		if (!value) return 'Not recorded';

		return new Intl.DateTimeFormat('en-GB', {
			dateStyle: 'medium',
			timeStyle: 'short'
		}).format(new Date(value));
	}

	function selectAllReasons(event: Event) {
		const input = event.currentTarget as HTMLInputElement;
		const form = input.form;

		if (!form || !input.checked) {
			return;
		}

		for (const field of form.querySelectorAll<HTMLInputElement>('input[name="refund_reasons"]')) {
			field.checked = false;
		}

		form.requestSubmit();
	}

	function selectSpecificReason(event: Event) {
		const input = event.currentTarget as HTMLInputElement;
		const form = input.form;

		if (!form) {
			return;
		}

		const allReasonsInput = form.querySelector<HTMLInputElement>('input[name="refund_reason_all"]');
		if (allReasonsInput) {
			allReasonsInput.checked = false;
		}

		form.requestSubmit();
	}

	function openEvidenceZoom(path: string | null) {
		const src = storagePathSrc(path);
		if (!src) return;
		zoomedEvidenceImage = src;
	}
</script>

<section class="space-y-6">
	<div class="panel">
		<p class="eyebrow">Admin</p>
		<h1 class="mt-2 text-3xl font-semibold">Order operations</h1>
		<p class="mt-3 text-sm text-slate-600">Choose the queue you want to work through, then narrow down refund requests by reason when needed.</p>
	</div>
	<form class="panel grid gap-4 lg:grid-cols-[minmax(0,1fr)_14rem_14rem_auto] lg:items-end" method="GET" action="/admin/orders">
		<label class="block">
			<span class="mb-1 block text-sm font-medium text-slate-700">Search orders</span>
			<input
				class="w-full rounded-2xl border-slate-300"
				name="search"
				placeholder="Order ID, buyer, or item"
				type="search"
				value={data.filters.search}
			/>
		</label>
		<label class="block">
			<span class="mb-1 block text-sm font-medium text-slate-700">Show queue</span>
			<select class="w-full rounded-2xl border-slate-300" name="queue" onchange={(event) => event.currentTarget.form?.requestSubmit()}>
				<option value="all" selected={data.filters.queue === 'all'}>All orders</option>
				<option value="shipping" selected={data.filters.queue === 'shipping'}>Orders waiting shipping</option>
				<option value="refund" selected={data.filters.queue === 'refund'}>Orders waiting refund</option>
			</select>
		</label>
		<label class="block">
			<span class="mb-1 block text-sm font-medium text-slate-700">Sort by</span>
			<select class="w-full rounded-2xl border-slate-300" name="sort" onchange={(event) => event.currentTarget.form?.requestSubmit()}>
				<option value="newest" selected={data.filters.sort === 'newest'}>Newest first</option>
				<option value="oldest" selected={data.filters.sort === 'oldest'}>Oldest first</option>
				<option value="total_low" selected={data.filters.sort === 'total_low'}>Total low to high</option>
				<option value="total_high" selected={data.filters.sort === 'total_high'}>Total high to low</option>
			</select>
		</label>
		<div class="flex flex-wrap gap-3">
			<button class="btn-primary rounded-full px-5 py-3 text-sm" type="submit">Apply</button>
			<a class="btn-secondary rounded-full px-5 py-3 text-sm" href="/admin/orders">Reset</a>
		</div>

		{#if data.filters.queue === 'refund'}
			<div class="lg:col-span-4">
				<span class="mb-2 block text-sm font-medium text-slate-700">Refund reasons</span>
				<div class="flex flex-wrap gap-3">
					<label class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700">
						<input
							checked={data.filters.refundReasons.length === 0}
							name="refund_reason_all"
							type="radio"
							value="all"
							onchange={selectAllReasons}
						/>
						All reasons
					</label>
					{#each refundReasons as reason}
						<label class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700">
							<input
								checked={data.filters.refundReasons.includes(reason.value)}
								name="refund_reasons"
								type="checkbox"
								value={reason.value}
								onchange={selectSpecificReason}
							/>
							{reason.label}
						</label>
					{/each}
				</div>
			</div>
		{/if}
	</form>
	{#if form?.error}
		<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
	{/if}
	{#if form?.success}
		<p class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{form.success}</p>
	{/if}
	{#if data.orders.data.length === 0}
		<div class="panel py-12 text-center">
			<p class="text-lg font-medium text-slate-800">No orders matched the current filters.</p>
			<p class="mt-2 text-sm text-slate-500">Try a different queue, buyer name, item, order ID, or refund reason filter.</p>
		</div>
	{:else}
		<div class="space-y-5">
			{#each data.orders.data as order}
				<article class="panel space-y-4">
					<div class="flex flex-wrap items-start justify-between gap-4">
						<div>
							<h2 class="text-xl font-semibold">Order ID {order.order_number ?? order.id}</h2>
							<p class="mt-1 text-sm text-slate-500">
								{order.buyer?.name ?? 'Unknown buyer'} · {order.status} · ฿{order.total_price.toFixed(2)}
							</p>
							<p class="mt-1 text-xs text-slate-500">Placed {formatDate(order.created_at)}</p>
						</div>
						<div class="flex gap-3">
							<a class="btn-secondary rounded-full px-4 py-2 text-sm" href={`/admin/orders/${order.id}`}>View details</a>
							{#if order.status === 'pending'}
								<form method="POST" action="?/ship">
									<input name="order_id" type="hidden" value={order.id} />
									<button class="btn-secondary rounded-full px-4 py-2 text-sm" type="submit">Ship</button>
								</form>
							{/if}
						</div>
					</div>
					<p class="text-sm text-slate-600">{order.shipping_address_formatted ?? order.shipping_address}</p>
					<div class="grid gap-3">
						{#each order.items as line}
							<div class="rounded-[1.25rem] border border-slate-200 p-4">
								<div class="flex flex-wrap items-start justify-between gap-3">
									<div>
										<a class="flex items-center gap-4 rounded-[1rem] transition hover:bg-slate-50" href={`/items/${line.item_id}`}>
											{#if line.item && itemImageSrc(line.item)}
												<img
													alt={line.item.name}
													class="h-20 w-20 rounded-[1rem] object-cover"
													src={itemImageSrc(line.item) ?? undefined}
												/>
											{:else}
												<div class="subtle-box flex h-20 w-20 items-center justify-center text-xs text-sky-500">No image</div>
											{/if}
											<div>
												<p class="font-medium transition-colors hover:text-blue-700">{line.item?.name ?? `Item #${line.item_id}`}</p>
												<p class="text-sm text-slate-500">{line.quantity} × ฿{line.price_at_purchase.toFixed(2)}</p>
											</div>
										</a>
									</div>
									<div class="text-right text-xs">
										{#if line.refunded_quantity > 0}
											<p class="font-medium text-emerald-700">Refunded: {line.refunded_quantity}</p>
										{/if}
										{#if line.refundable_quantity > 0}
											<p class="text-slate-500">Still refundable: {line.refundable_quantity}</p>
										{/if}
									</div>
								</div>
								{#if line.refund_requested_quantity > 0}
									<div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
										<p class="font-medium">Pending refund request</p>
										<p class="mt-1">
											Quantity: {line.refund_requested_quantity}
											{#if line.refund_reason_code}
												· Reason:
												{line.refund_reason_code === 'other'
													? (line.refund_reason_detail || 'Other')
													: line.refund_reason_code.replaceAll('_', ' ')}
											{/if}
										</p>
										{#if line.refund_issue_description}
											<p class="mt-2 text-amber-800">{line.refund_issue_description}</p>
										{/if}
										{#if storagePathSrc(line.refund_evidence_image_path)}
											<button class="mt-3 block text-left" type="button" onclick={() => openEvidenceZoom(line.refund_evidence_image_path)}>
												<img
													alt="Refund evidence"
													class="h-32 w-full max-w-xs rounded-[1rem] object-cover transition hover:opacity-90"
													src={storagePathSrc(line.refund_evidence_image_path) ?? undefined}
												/>
												<span class="mt-2 block text-xs text-amber-700">Click to zoom</span>
											</button>
										{/if}
										{#if line.refund_requested_at}
											<p class="mt-2 text-xs text-amber-700">Requested on {formatDate(line.refund_requested_at)}</p>
										{/if}
									</div>
								{/if}
								{#if line.refunded_quantity > 0}
									<div class="mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900">
										<p class="font-medium">Approved refund</p>
										<p class="mt-1">Quantity: {line.refunded_quantity}</p>
										{#if line.refund_approved_at}
											<p class="mt-2 text-xs text-emerald-700">Approved on {formatDate(line.refund_approved_at)}</p>
										{/if}
									</div>
								{/if}
							</div>
						{/each}
					</div>
				</article>
			{/each}
		</div>
	{/if}
	<Pagination basePath={paginationBasePath} meta={data.orders.meta} />
</section>

{#if zoomedEvidenceImage}
	<div class="fixed inset-0 z-50 flex items-center justify-center p-6">
		<button aria-label="Close image zoom" class="absolute inset-0 bg-slate-950/85" type="button" onclick={() => (zoomedEvidenceImage = null)}></button>
		<div class="relative z-10 max-h-full max-w-5xl">
			<button class="absolute right-3 top-3 rounded-full bg-white/90 px-3 py-1 text-sm font-medium text-slate-900" type="button" onclick={() => (zoomedEvidenceImage = null)}>
				Close
			</button>
			<img alt="Refund evidence zoomed" class="max-h-[85vh] max-w-full rounded-[1.5rem] object-contain shadow-2xl" src={zoomedEvidenceImage} />
		</div>
	</div>
{/if}
