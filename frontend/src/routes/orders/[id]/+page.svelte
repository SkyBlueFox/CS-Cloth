<script lang="ts">
    import { itemImageSrc } from '$lib/media';
    import { fly } from 'svelte/transition';

    interface TimelineEvent {
        key: string;
        title: string;
        detail: string;
        timestamp: string;
    }

    let { data, form } = $props();

    const refundReasons = [
        { value: 'damaged_item', label: 'Defective / Damaged' },
        { value: 'wrong_item', label: 'Wrong Item Received' },
        { value: 'missing_parts', label: 'Missing Parts or Accessories' },
        { value: 'not_as_described', label: 'Not as Described' },
        { value: 'quality_issue', label: 'Quality Issue' },
        { value: 'changed_mind', label: 'Changed My Mind' },
        { value: 'other', label: 'Other' }
    ];

    function formatDate(value: string | null) {
        if (!value) return 'N/A';
        return new Intl.DateTimeFormat('th-TH', {
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

    function lineItemName(line: (typeof data.order.items)[number]) {
        return line.item?.name ?? `Item #${line.item_id}`;
    }

    const timelineEvents: TimelineEvent[] = [
        ...data.order.status_history
            .filter((event) => ['placed', 'shipped', 'cancelled'].includes(event.key))
            .map((event) => ({
                key: `status-${event.key}-${event.timestamp}`,
                title:
                    event.key === 'placed'
                        ? 'Order placed'
                        : event.key === 'shipped'
                            ? 'Order shipped'
                            : 'Order cancelled',
                detail:
                    event.key === 'placed'
                        ? `Order ${data.order.order_number} was placed successfully.`
                        : event.key === 'shipped'
                            ? `Your order was handed to ${data.order.delivery_method_label ?? 'the courier'}.`
                            : 'This order was cancelled and refunded back to your wallet.',
                timestamp: event.timestamp
            })),
        ...data.order.items.flatMap((line) =>
            line.refund_events.map((event) => ({
                key: `refund-${event.id}`,
                title:
                    event.event_type === 'requested'
                        ? 'Refund requested'
                        : event.event_type === 'approved'
                            ? 'Refund approved'
                            : event.event_type === 'dismissed'
                                ? 'Refund dismissed'
                                : 'Refund updated',
                detail:
                    event.event_type === 'requested'
                        ? `${event.quantity}x ${lineItemName(line)} requested for refund${refundReasonText(event.reason_code, event.reason_detail) ? ` because: ${refundReasonText(event.reason_code, event.reason_detail)}.` : '.'}`
                        : event.event_type === 'approved'
                            ? `${event.quantity}x ${lineItemName(line)} approved for refund.`
                            : event.event_type === 'dismissed'
                                ? `${event.quantity}x ${lineItemName(line)} refund request was dismissed by the store.`
                                : `${event.quantity}x ${lineItemName(line)} refund was updated.`,
                timestamp: event.happened_at ?? ''
            }))
        )
    ]
        .filter((event) => !!event.timestamp)
        .sort((a, b) => new Date(b.timestamp).getTime() - new Date(a.timestamp).getTime());
</script>

<section class="mx-auto max-w-7xl space-y-10">
    <div class="panel flex flex-wrap items-center justify-between gap-8 border-none bg-slate-900 text-white shadow-2xl">
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <span class="h-1.5 w-10 rounded-full bg-blue-500"></span>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-400">Order Management</p>
            </div>
            <h1 class="text-4xl font-black tracking-tight uppercase">Order #{data.order.order_number}</h1>
            <div class="flex items-center gap-4">
                <span class="rounded-full bg-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-slate-900 ring-1 ring-white/70">
                    Status: {data.order.status}
                </span>
                <span class="text-lg font-black text-white">฿{data.order.total_price.toLocaleString()}</span>
            </div>
        </div>
        <div class="flex gap-4">
            <a class="rounded-2xl bg-white/10 px-8 py-4 text-xs font-black uppercase tracking-widest text-white backdrop-blur transition-all hover:bg-white/20" href="/orders">Back</a>
            {#if data.order.status === 'pending'}
                <form method="POST" action="?/cancel">
                    <button class="rounded-2xl bg-rose-600 px-8 py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-rose-600/20 transition-all hover:bg-rose-700" type="submit">Cancel Order</button>
                </form>
            {/if}
        </div>
    </div>

    {#if form?.error || form?.success}
        <div in:fly={{ y: -10 }} class="rounded-[2rem] shadow-lg overflow-hidden">
            {#if form?.error} <p class="bg-rose-50 px-8 py-5 text-sm font-black text-rose-800 ring-1 ring-rose-200">{form.error}</p> {/if}
            {#if form?.success} <p class="bg-emerald-50 px-8 py-5 text-sm font-black text-emerald-800 ring-1 ring-emerald-200">{form.success}</p> {/if}
        </div>
    {/if}

    <div class="grid gap-10 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="space-y-8">
            <section class="panel p-10">
                <header class="mb-10">
                    <p class="eyebrow text-blue-600">Manifest</p>
                    <h2 class="mt-2 text-3xl font-black text-slate-900 tracking-tight">Order Items</h2>
                </header>

                <div class="grid gap-6">
                    {#each data.order.items as line (line.id)}
                        <div class="group relative rounded-[2.5rem] bg-slate-50/50 p-8 ring-1 ring-slate-100 transition-all hover:bg-white hover:shadow-2xl hover:shadow-blue-900/5">
                            <a class="flex flex-col gap-8 md:flex-row md:items-start" href={`/items/${line.item_id}`}>
                                <div class="h-32 w-32 shrink-0 overflow-hidden rounded-[1.5rem] bg-white shadow-md ring-1 ring-slate-200">
                                    {#if line.item}
                                        <img alt="" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" src={itemImageSrc(line.item)} />
                                    {/if}
                                </div>
                                <div class="flex-1 space-y-2">
                                    <div class="flex flex-wrap items-start justify-between gap-4">
                                        <h3 class="text-xl font-black uppercase tracking-tight text-slate-900 group-hover:text-blue-700">{line.item?.name}</h3>
                                        <p class="text-xl font-black text-blue-700">฿{(line.price_at_purchase * line.quantity).toLocaleString()}</p>
                                    </div>
                                    <p class="text-sm font-bold uppercase tracking-widest text-slate-500">{line.quantity} Unit(s) × ฿{line.price_at_purchase.toLocaleString()}</p>
                                    <p class="text-sm font-medium leading-relaxed text-slate-600">{line.item?.description}</p>
                                </div>
                            </a>

                            {#if (data.order.status === 'shipped' || data.order.status === 'partially_refunded') && line.refundable_quantity > 0}
                                <details class="mt-8 border-t border-slate-100 pt-8 group/refund">
                                    <summary class="flex cursor-pointer list-none items-center gap-3 text-[10px] font-black uppercase tracking-[0.3em] text-amber-600 transition-colors hover:text-amber-700">
                                        <svg class="h-4 w-4 transition-transform group-open/refund:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M19 9l-7 7-7-7" /></svg>
                                        Initiate Item Refund
                                    </summary>
                                    <form class="mt-8 grid gap-6" method="POST" enctype="multipart/form-data" action="?/refund">
                                        <input name="order_item_id" type="hidden" value={line.id} />
                                        <div class="grid gap-6 md:grid-cols-2">
                                            <label class="block group">
                                                <span class="text-slate-700">Return Quantity</span>
                                                <select class="mt-2 w-full font-bold text-slate-900" name="quantity">
                                                    {#each Array.from({ length: line.refundable_quantity }, (_, i) => i + 1) as q (q)}
                                                        <option value={q}>{q}</option>
                                                    {/each}
                                                </select>
                                            </label>
                                            <label class="block group">
                                                <span class="text-slate-700">Reason for Return</span>
                                                <select class="mt-2 w-full font-bold text-slate-900" name="reason_code">
                                    {#each refundReasons as r (r.value)}<option value={r.value}>{r.label}</option>{/each}
                                                </select>
                                            </label>
                                        </div>
                                        <label class="block group">
                                            <span class="text-slate-700">Details of the issue</span>
                                            <textarea class="mt-2 w-full font-bold text-slate-900" name="issue_description" rows="3" placeholder="Explain the defect or problem..." required></textarea>
                                        </label>
                                        <div class="flex flex-col gap-6 md:flex-row md:items-end">
                                            <label class="flex-1 group">
                                                <span class="text-slate-700">Evidence Image</span>
                                                <input class="mt-2 w-full font-bold text-slate-900" name="evidence_image" type="file" accept="image/*" required />
                                            </label>
                                            <button class="btn-primary bg-amber-600 px-10 shadow-amber-600/20 hover:bg-amber-700" type="submit">Submit Request</button>
                                        </div>
                                    </form>
                                </details>
                            {/if}
                        </div>
                    {/each}
                </div>
            </section>
        </div>

        <div class="space-y-8">
            <section class="panel bg-slate-50/50">
                <header class="mb-10 border-b border-slate-200 pb-6">
                    <p class="eyebrow text-slate-500">Traceability</p>
                    <h3 class="mt-2 text-2xl font-black uppercase tracking-tight text-slate-900">Timeline</h3>
                </header>
                <div class="relative space-y-8">
                    <div class="absolute bottom-2 left-1.5 top-2 w-0.5 bg-slate-200"></div>
                    {#if timelineEvents.length === 0}
                        <p class="text-sm font-bold text-slate-500">No status updates recorded yet.</p>
                    {/if}
                    {#each timelineEvents as event (event.key)}
                        <div class="relative pl-10">
                            <div class="absolute left-0 top-1.5 h-3.5 w-3.5 rounded-full border-2 border-white bg-blue-600 shadow-md ring-4 ring-blue-50"></div>
                            <p class="text-sm font-black uppercase tracking-tight text-slate-900">{event.title}</p>
                            <p class="mt-1 text-sm font-medium leading-relaxed text-slate-600">{event.detail}</p>
                            <p class="mt-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">{formatDate(event.timestamp)}</p>
                        </div>
                    {/each}
                </div>
            </section>

            <section class="panel">
                <header class="mb-6">
                    <p class="eyebrow text-slate-500">Destination</p>
                    <h3 class="mt-2 text-xl font-black uppercase tracking-tight text-slate-900">Delivery Info</h3>
                </header>
                <div class="space-y-4 rounded-2xl bg-slate-50 p-6 ring-1 ring-slate-100">
                    <p class="font-serif text-sm font-bold italic leading-relaxed text-slate-800">
                        {data.order.shipping_address_formatted ?? data.order.shipping_address}
                    </p>
                    <div class="space-y-2 border-t border-slate-200 pt-4">
                        <div class="flex justify-between text-[11px] font-bold uppercase text-slate-400">
                            <span>Courier</span>
                            <span class="text-slate-900">{data.order.delivery_method_label ?? 'Not specified'}</span>
                        </div>
                        <div class="flex justify-between text-[11px] font-bold uppercase text-slate-400">
                            <span>Placed</span>
                            <span class="text-slate-900">{formatDate(data.order.created_at)}</span>
                        </div>
                        <div class="flex justify-between text-[11px] font-bold uppercase text-slate-400">
                            <span>Updated</span>
                            <span class="text-slate-900">{formatDate(data.order.updated_at)}</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</section>
