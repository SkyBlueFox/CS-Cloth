<script lang="ts">
	import { itemImageSrc, storagePathSrc } from '$lib/media';

	let { data, form } = $props();
	const refundReasons = [
		{ value: 'damaged_item', label: 'Item arrived damaged' },
		{ value: 'wrong_item', label: 'Wrong item received' },
		{ value: 'missing_parts', label: 'Missing parts or accessories' },
		{ value: 'not_as_described', label: 'Item not as described' },
		{ value: 'quality_issue', label: 'Quality issue' },
		{ value: 'changed_mind', label: 'No longer needed' },
		{ value: 'other', label: 'Other' }
	];
	const refundReasonLabels = Object.fromEntries(refundReasons.map((reason) => [reason.value, reason.label]));

	function formatDate(value: string | null) {
		if (!value) return 'Not recorded';

		return new Intl.DateTimeFormat('en-GB', {
			dateStyle: 'medium',
			timeStyle: 'short'
		}).format(new Date(value));
	}

	function refundReasonText(line: (typeof data.order.items)[number]) {
		if (!line.refund_reason_code) return null;

		if (line.refund_reason_code === 'other') {
			return line.refund_reason_detail || 'Other';
		}

		return refundReasonLabels[line.refund_reason_code] ?? line.refund_reason_code.replaceAll('_', ' ');
	}

	const timelineEvents = $derived.by(() => {
		const events: { key: string; title: string; detail: string; timestamp: string }[] = [];

		if (data.order.created_at) {
			events.push({
				key: 'placed',
				title: 'Order placed',
				detail: `Order ${data.order.order_number} was placed successfully.`,
				timestamp: data.order.created_at
			});
		}

		if (data.order.shipped_at) {
			events.push({
				key: 'shipped',
				title: 'Order shipped',
				detail: 'Your order was packed and marked as shipped.',
				timestamp: data.order.shipped_at
			});
		}

		for (const line of data.order.items) {
			if (line.refund_requested_at && line.refund_requested_quantity > 0) {
				const reason = refundReasonText(line);
				events.push({
					key: `refund-requested-${line.id}`,
					title: 'Refund requested',
					detail: `${line.refund_requested_quantity}x ${line.item?.name ?? `Item #${line.item_id}`} requested for refund${reason ? ` because: ${reason}` : ''}.`,
					timestamp: line.refund_requested_at
				});
			}

			if (line.refund_approved_at && line.refunded_quantity > 0) {
				events.push({
					key: `refund-approved-${line.id}`,
					title: 'Refund approved',
					detail: `${line.refunded_quantity}x ${line.item?.name ?? `Item #${line.item_id}`} approved for refund.`,
					timestamp: line.refund_approved_at
				});
			}
		}

		if (data.order.cancelled_at) {
			events.push({
				key: 'cancelled',
				title: 'Order cancelled',
				detail: 'The order was cancelled and refunded back to your balance.',
				timestamp: data.order.cancelled_at
			});
		}

		return events.sort((a, b) => new Date(a.timestamp).getTime() - new Date(b.timestamp).getTime());
	});
</script>

<section class="space-y-6">
	<div class="panel flex flex-wrap items-start justify-between gap-4">
		<div>
			<p class="eyebrow">Order Detail</p>
			<h1 class="mt-2 text-3xl font-semibold">Order ID {data.order.order_number}</h1>
			<p class="mt-2 text-sm text-slate-500">
				Current status: {data.order.status} · Total ฿{data.order.total_price.toFixed(2)}
			</p>
		</div>
		<div class="flex flex-wrap gap-3">
			<a class="btn-secondary rounded-full px-4 py-2 text-sm" href="/orders">Back to orders</a>
			{#if data.order.status === 'pending'}
				<form method="POST" action="?/cancel">
					<button class="rounded-full border border-rose-300 px-4 py-2 text-sm text-rose-700" type="submit">Cancel order</button>
				</form>
			{/if}
		</div>
	</div>

	{#if form?.error}
		<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
	{/if}
	{#if form?.success}
		<p class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{form.success}</p>
	{/if}

	<div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
		<div class="panel space-y-4">
			<div>
				<p class="eyebrow">Items</p>
				<h2 class="mt-2 text-2xl font-semibold">Products in this order</h2>
			</div>
			<div class="space-y-4">
				{#each data.order.items as line (line.id)}
					<div class="rounded-[1.5rem] border border-slate-200 p-4">
						<div class="flex items-center gap-4">
							{#if line.item && itemImageSrc(line.item)}
								<img
									alt={line.item.name}
									class="h-24 w-24 rounded-[1.3rem] object-cover"
									src={itemImageSrc(line.item) ?? undefined}
								/>
							{:else}
								<div class="subtle-box flex h-24 w-24 items-center justify-center text-xs text-sky-500">No image</div>
							{/if}
							<div class="space-y-1">
								<p class="text-lg font-semibold text-slate-900">{line.item?.name ?? `Item #${line.item_id}`}</p>
								<p class="text-sm text-slate-500">{line.quantity} × ฿{line.price_at_purchase.toFixed(2)}</p>
								{#if line.refunded_quantity > 0}
									<p class="text-sm font-medium text-emerald-700">
										Refunded quantity: {line.refunded_quantity}
									</p>
								{/if}
								{#if line.refund_requested_quantity > 0}
									<p class="text-sm font-medium text-amber-700">
										Pending refund quantity: {line.refund_requested_quantity}
									</p>
								{/if}
								{#if line.item?.description}
									<p class="text-sm text-slate-600">{line.item.description}</p>
								{/if}
							</div>
						</div>

						{#if (data.order.status === 'shipped' || data.order.status === 'partially_refunded') && line.refundable_quantity > 0}
							<form class="mt-5 grid gap-3 rounded-[1.25rem] border border-amber-200 bg-amber-50/60 p-4" method="POST" action="?/refund">
								<input name="order_item_id" type="hidden" value={line.id} />
								<div>
									<p class="text-sm font-semibold text-amber-900">Request a refund for this item</p>
									<p class="mt-1 text-xs text-amber-800">
										Common e-commerce flow: choose the specific item and quantity you want refunded.
									</p>
								</div>
								<div class="grid gap-3 md:grid-cols-2">
									<label class="block">
										<span class="mb-1 block text-sm font-medium text-slate-700">Quantity to refund</span>
										<select class="w-full rounded-2xl border-slate-300" name="quantity">
											{#each Array.from({ length: line.refundable_quantity }, (_, index) => index + 1) as quantity}
												<option value={quantity}>{quantity}</option>
											{/each}
										</select>
									</label>
									<label class="block">
										<span class="mb-1 block text-sm font-medium text-slate-700">Reason</span>
										<select class="w-full rounded-2xl border-slate-300" name="reason_code">
											{#each refundReasons as reason}
												<option value={reason.value}>{reason.label}</option>
											{/each}
										</select>
									</label>
								</div>
								<label class="block">
									<span class="mb-1 block text-sm font-medium text-slate-700">If you chose Other, add a short reason</span>
									<input class="w-full rounded-2xl border-slate-300" name="reason_detail" placeholder="Optional unless you selected Other" />
								</label>
								<label class="block">
									<span class="mb-1 block text-sm font-medium text-slate-700">Explain what is wrong with the product</span>
									<textarea
										class="w-full rounded-2xl border-slate-300"
										name="issue_description"
										rows="4"
										minlength="10"
										maxlength="2000"
										placeholder="Describe the defect, damage, wrong item, or problem you received"
										required
									></textarea>
								</label>
								<label class="block">
									<span class="mb-1 block text-sm font-medium text-slate-700">Upload a photo of the issue</span>
									<input
										accept="image/*"
										class="w-full rounded-2xl border-slate-300"
										name="evidence_image"
										type="file"
										required
									/>
								</label>
								<button class="btn-warn w-fit" type="submit">Request refund for this item</button>
							</form>
						{/if}

						{#if line.refund_requested_quantity > 0}
							<div class="mt-4 rounded-[1.25rem] border border-amber-200 bg-amber-50/60 p-4 text-sm text-amber-900">
								<p class="font-medium">Pending refund request</p>
								<p class="mt-1">
									Quantity: {line.refund_requested_quantity}
									{#if line.refund_reason_code}
										· Reason: {line.refund_reason_code === 'other' ? (line.refund_reason_detail || 'Other') : line.refund_reason_code.replaceAll('_', ' ')}
									{/if}
								</p>
								{#if line.refund_issue_description}
									<p class="mt-2 text-amber-800">{line.refund_issue_description}</p>
								{/if}
								{#if storagePathSrc(line.refund_evidence_image_path)}
									<img
										alt="Refund evidence"
										class="mt-3 h-40 w-full max-w-sm rounded-[1rem] object-cover"
										src={storagePathSrc(line.refund_evidence_image_path) ?? undefined}
									/>
								{/if}
								{#if line.refund_requested_at}
									<p class="mt-2 text-xs text-amber-700">Requested on {formatDate(line.refund_requested_at)}</p>
								{/if}
							</div>
						{/if}
					</div>
				{/each}
			</div>
		</div>

		<div class="space-y-6">
			<div class="panel space-y-4">
				<div>
					<p class="eyebrow">Timeline</p>
					<h2 class="mt-2 text-2xl font-semibold">Status updates</h2>
				</div>
				<div class="space-y-4">
					{#each timelineEvents as event (event.key)}
						<div class="flex gap-4">
							<div class="mt-1 h-3 w-3 rounded-full bg-blue-700"></div>
							<div>
								<p class="font-medium text-slate-900">{event.title}</p>
								<p class="mt-1 text-sm text-slate-600">{event.detail}</p>
								<p class="text-sm text-slate-500">{formatDate(event.timestamp)}</p>
							</div>
						</div>
					{/each}
				</div>
			</div>

			<div class="panel space-y-3">
				<div>
					<p class="eyebrow">Shipping</p>
					<h2 class="mt-2 text-2xl font-semibold">Delivery details</h2>
				</div>
				<p class="text-sm text-slate-600">{data.order.shipping_address_formatted ?? data.order.shipping_address}</p>
				<div class="grid gap-2 text-sm text-slate-500">
					<p>Placed: {formatDate(data.order.created_at)}</p>
					<p>Last updated: {formatDate(data.order.updated_at)}</p>
					{#if data.order.shipped_at}
						<p>Shipped: {formatDate(data.order.shipped_at)}</p>
					{/if}
					{#if data.order.refund_requested_at}
						<p>Refund requested: {formatDate(data.order.refund_requested_at)}</p>
					{/if}
					{#if data.order.refunded_at}
						<p>Refund completed: {formatDate(data.order.refunded_at)}</p>
					{/if}
					{#if data.order.cancelled_at}
						<p>Cancelled: {formatDate(data.order.cancelled_at)}</p>
					{/if}
				</div>
			</div>
		</div>
	</div>
</section>
