<script lang="ts">
	import { itemImageSrc, storagePathSrc } from '$lib/media';

	let { data, form } = $props();
	let zoomedEvidenceImage = $state<string | null>(null);

	function formatDate(value: string | null) {
		if (!value) return 'Not recorded';

		return new Intl.DateTimeFormat('en-GB', {
			dateStyle: 'medium',
			timeStyle: 'short'
		}).format(new Date(value));
	}

	const refundReasonLabels: Record<string, string> = {
		damaged_item: 'Item arrived damaged',
		wrong_item: 'Wrong item received',
		missing_parts: 'Missing parts or accessories',
		not_as_described: 'Item not as described',
		quality_issue: 'Quality issue',
		changed_mind: 'No longer needed',
		other: 'Other'
	};

	function refundReasonText(reasonCode: string | null, reasonDetail: string | null) {
		if (!reasonCode) return null;
		if (reasonCode === 'other') return reasonDetail || 'Other';
		return refundReasonLabels[reasonCode] ?? reasonCode.replaceAll('_', ' ');
	}

	function openEvidenceZoom(path: string | null) {
		const src = storagePathSrc(path);
		if (!src) return;
		zoomedEvidenceImage = src;
	}
</script>

<section class="space-y-6">
	<div class="panel flex flex-wrap items-start justify-between gap-4">
		<div>
			<p class="eyebrow">Admin Order Detail</p>
			<h1 class="mt-2 text-3xl font-semibold">Order ID {data.order.order_number}</h1>
			<p class="mt-2 text-sm text-slate-500">
				{data.order.buyer?.name ?? 'Unknown buyer'} · {data.order.status} · ฿{data.order.total_price.toFixed(2)}
			</p>
		</div>
		<div class="flex flex-wrap gap-3">
			<a class="btn-secondary rounded-full px-4 py-2 text-sm" href="/admin/orders">Back to orders</a>
			{#if data.order.status === 'pending'}
				<form method="POST" action="?/ship">
					<button class="btn-secondary rounded-full px-4 py-2 text-sm" type="submit">Ship order</button>
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
				<h2 class="mt-2 text-2xl font-semibold">Review refund requests</h2>
			</div>
			<div class="space-y-4">
				{#each data.order.items as line (line.id)}
					<div class="rounded-[1.5rem] border border-slate-200 p-4">
						<div class="flex items-start gap-4">
							{#if line.item && itemImageSrc(line.item)}
								<img
									alt={line.item.name}
									class="h-24 w-24 rounded-[1.2rem] object-cover"
									src={itemImageSrc(line.item) ?? undefined}
								/>
							{:else}
								<div class="subtle-box flex h-24 w-24 items-center justify-center text-xs text-sky-500">No image</div>
							{/if}
							<div class="space-y-1">
								<p class="text-lg font-semibold text-slate-900">{line.item?.name ?? `Item #${line.item_id}`}</p>
								<p class="text-sm text-slate-500">{line.quantity} × ฿{line.price_at_purchase.toFixed(2)}</p>
								{#if line.refunded_quantity > 0}
									<p class="text-sm font-medium text-emerald-700">Already refunded: {line.refunded_quantity}</p>
								{/if}
								{#if line.refundable_quantity > 0}
									<p class="text-sm text-slate-500">Still refundable: {line.refundable_quantity}</p>
								{/if}
							</div>
						</div>

						{#if line.refund_requested_quantity > 0}
							<div class="mt-4 rounded-[1.25rem] border border-amber-200 bg-amber-50/70 p-4">
								<p class="font-medium text-amber-900">Pending refund request</p>
								<p class="mt-2 text-sm text-amber-900">
									Quantity: {line.refund_requested_quantity}
									{#if line.refund_reason_code}
										· Reason:
										{line.refund_reason_code === 'other'
											? (line.refund_reason_detail || 'Other')
											: line.refund_reason_code.replaceAll('_', ' ')}
									{/if}
								</p>
								{#if line.refund_issue_description}
									<p class="mt-3 text-sm text-amber-800">{line.refund_issue_description}</p>
								{/if}
								{#if storagePathSrc(line.refund_evidence_image_path)}
									<button class="mt-4 block text-left" type="button" onclick={() => openEvidenceZoom(line.refund_evidence_image_path)}>
										<img
											alt="Refund evidence"
											class="h-48 w-full max-w-md rounded-[1rem] object-cover transition hover:opacity-90"
											src={storagePathSrc(line.refund_evidence_image_path) ?? undefined}
										/>
										<span class="mt-2 block text-xs text-amber-700">Click to zoom</span>
									</button>
								{/if}
								<div class="mt-4 flex flex-wrap items-center justify-between gap-3">
									<p class="text-xs text-amber-700">Requested on {formatDate(line.refund_requested_at)}</p>
									<div class="flex flex-wrap gap-3">
										<form method="POST" action="?/dismissRefund">
											<input name="order_item_id" type="hidden" value={line.id} />
											<button class="btn-secondary rounded-full px-4 py-2 text-sm" type="submit">Dismiss request</button>
										</form>
										<form method="POST" action="?/refund">
											<input name="order_item_id" type="hidden" value={line.id} />
											<button class="btn-warn rounded-full px-4 py-2 text-sm" type="submit">Approve this refund</button>
										</form>
									</div>
								</div>
							</div>
						{/if}

						{#if line.refunded_quantity > 0}
							<div class="mt-4 rounded-[1.25rem] border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
								<p class="font-medium">Approved refund</p>
								<p class="mt-2">Quantity: {line.refunded_quantity}</p>
								{#if line.refund_approved_at}
									<p class="mt-2 text-xs text-emerald-700">Approved on {formatDate(line.refund_approved_at)}</p>
								{/if}
							</div>
						{/if}

						{#if line.refund_events.length > 0}
							<div class="mt-4 rounded-[1.25rem] border border-slate-200 bg-slate-50 p-4">
								<p class="font-medium text-slate-900">Refund history</p>
								<div class="mt-3 space-y-3">
									{#each line.refund_events as event (event.id)}
										<div class="rounded-2xl border border-slate-200 bg-white p-3 text-sm text-slate-700">
											<p class="font-medium text-slate-900">
												{event.event_type === 'requested'
													? 'Refund requested'
													: event.event_type === 'approved'
														? 'Refund approved'
														: event.event_type === 'dismissed'
															? 'Refund dismissed'
															: event.event_type}
											</p>
											<p class="mt-1">Quantity: {event.quantity}</p>
											{#if refundReasonText(event.reason_code, event.reason_detail)}
												<p class="mt-1">Reason: {refundReasonText(event.reason_code, event.reason_detail)}</p>
											{/if}
											{#if event.issue_description}
												<p class="mt-2 text-slate-600">{event.issue_description}</p>
											{/if}
											{#if storagePathSrc(event.evidence_image_path)}
												<button class="mt-3 block text-left" type="button" onclick={() => openEvidenceZoom(event.evidence_image_path)}>
													<img
														alt="Refund evidence"
														class="h-40 w-full max-w-sm rounded-[1rem] object-cover transition hover:opacity-90"
														src={storagePathSrc(event.evidence_image_path) ?? undefined}
													/>
													<span class="mt-2 block text-xs text-slate-500">Click to zoom</span>
												</button>
											{/if}
											<p class="mt-2 text-xs text-slate-500">{formatDate(event.happened_at)}</p>
										</div>
									{/each}
								</div>
							</div>
						{/if}
					</div>
				{/each}
			</div>
		</div>

		<div class="space-y-6">
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
						<p>Latest refund activity: {formatDate(data.order.refund_requested_at)}</p>
					{/if}
					{#if data.order.refunded_at}
						<p>Latest refund approval: {formatDate(data.order.refunded_at)}</p>
					{/if}
				</div>
			</div>
		</div>
	</div>
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
