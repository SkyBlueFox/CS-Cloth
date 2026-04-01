<script lang="ts">
    import { itemImageSrc } from '$lib/media';
    import { fly } from 'svelte/transition';

    interface TimelineEvent {
        key: string;
        title: string;
        detail: string;
        timestamp: string | null;
    }

    let { data, form } = $props();
    let selectedRefundReasons = $state<Record<number, string>>({});

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

    function selectedReasonFor(lineId: number) {
        return selectedRefundReasons[lineId] ?? refundReasons[0]?.value ?? '';
    }

    let timelineEvents: TimelineEvent[] = [];

    $effect(() => {
        timelineEvents = [
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
                            ? `Your order was handed to courier.`
                            : 'Order cancelled and refunded.',
                    timestamp: event.timestamp
                })),

            ...(data.order.items ?? []).flatMap((line: typeof data.order.items[number]) =>
                (line.refund_events ?? []).map((event: typeof line.refund_events[number]) => ({
                    key: `refund-${event.id}`,
                    title: 'Refund Update',
                    detail: `${event.quantity}x ${
                        line.item?.name ?? 'Item'
                    } refund status: ${event.event_type}`,
                    timestamp: event.happened_at
                }))
            )
        ].sort(
            (a, b) =>
                new Date(b.timestamp ?? 0).getTime() -
                new Date(a.timestamp ?? 0).getTime()
        );
    });
</script>

<section class="mx-auto max-w-7xl space-y-10">
    <div class="panel flex flex-wrap items-center justify-between gap-8 border-none bg-slate-900 text-white shadow-2xl">
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <span class="h-1.5 w-10 rounded-full bg-blue-500"></span>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-400">
                    Order Management
                </p>
            </div>

            <h1 class="text-4xl font-black tracking-tight uppercase">
                Order #{data.order.order_number}
            </h1>

            <div class="flex items-center gap-4">
                <span class="rounded-full bg-white px-4 py-1.5 text-[10px] font-black uppercase text-slate-900">
                    Status: {data.order.status?.replace('_', ' ')}
                </span>
                <span class="text-lg font-black">
                    ฿{data.order.total_price?.toLocaleString()}
                </span>
            </div>
        </div>

        <div class="flex gap-4">
            <a
                class="rounded-2xl bg-white/10 px-8 py-4 text-xs font-black uppercase tracking-widest text-white backdrop-blur transition-all hover:bg-white/20"
                href="/orders"
            >
                Back
            </a>

            {#if data.order.status === 'pending'}
                <form method="POST" action="?/cancel">
                    <button
                        class="rounded-2xl bg-rose-600 px-8 py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-rose-600/20 transition-all hover:bg-rose-700"
                    >
                        Cancel Order
                    </button>
                </form>
            {/if}
        </div>
    </div>

    {#if form?.error || form?.success}
        <div in:fly={{ y: -10 }} class="rounded-4xl shadow-lg overflow-hidden">
            {#if form?.error}
                <p class="bg-rose-50 px-8 py-5 text-sm font-black text-rose-800">
                    {form.error}
                </p>
            {/if}
            {#if form?.success}
                <p class="bg-emerald-50 px-8 py-5 text-sm font-black text-emerald-800">
                    {form.success}
                </p>
            {/if}
        </div>
    {/if}

    <div class="grid gap-10 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="space-y-8">
            <section class="panel p-10">
                <h2 class="mb-10 text-3xl font-black text-slate-900 tracking-tight">
                    Order Items
                </h2>

                <div class="grid gap-6">
                    {#each data.order.items ?? [] as line (line.id)}
                        <div class="group relative rounded-[2.5rem] bg-slate-50/50 p-8 ring-1 ring-slate-100 transition-all hover:bg-white">
                            <div class="flex flex-col gap-8 md:flex-row">
                                <div class="h-32 w-32 shrink-0 overflow-hidden rounded-3xl bg-white ring-1 ring-slate-200">
                                    {#if line.item}
                                        <img
                                            alt=""
                                            class="h-full w-full object-cover"
                                            src={itemImageSrc(line.item)}
                                        />
                                    {/if}
                                </div>

                                <div class="flex-1 space-y-2">
                                    <div class="flex justify-between">
                                        <h3 class="text-xl font-black text-slate-900 uppercase">
                                            {line.item?.name ?? 'Unknown Item'}
                                        </h3>

                                        <p class="text-xl font-black text-blue-700">
                                            ฿{(
                                                line.price_at_purchase *
                                                line.quantity
                                            ).toLocaleString()}
                                        </p>
                                    </div>

                                    <p class="text-sm font-bold text-slate-500">
                                        {line.quantity} Unit(s) × ฿
                                        {line.price_at_purchase.toLocaleString()}
                                    </p>
                                </div>
                            </div>

                            {#if (data.order.status === 'shipped' || data.order.status === 'completed') && line.refundable_quantity > 0}
                                <details class="mt-8 border-t border-slate-100 pt-8">
                                    <summary class="cursor-pointer text-[10px] font-black uppercase text-amber-600">
                                        Initiate Item Refund
                                    </summary>

                                    <form
                                        class="mt-8 grid gap-6"
                                        method="POST"
                                        enctype="multipart/form-data"
                                        action="?/refund"
                                    >
                                        <input
                                            name="order_item_id"
                                            type="hidden"
                                            value={line.id}
                                        />

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
                                                <select
                                                    class="mt-2 w-full font-bold text-slate-900"
                                                    name="reason_code"
                                                    bind:value={selectedRefundReasons[line.id]}
                                                >
                                    {#each refundReasons as r (r.value)}<option value={r.value}>{r.label}</option>{/each}
                                                </select>
                                            </label>
                                        </div>
                                        {#if selectedReasonFor(line.id) === 'other'}
                                            <label class="block group">
                                                <span class="text-slate-700">Other Reason</span>
                                                <input
                                                    class="mt-2 w-full font-bold text-slate-900"
                                                    name="reason_detail"
                                                    type="text"
                                                    placeholder="Tell us the specific reason..."
                                                    required
                                                />
                                            </label>
                                        {/if}
                                        <label class="block group">
                                            <span class="text-slate-700">Details of the issue</span>
                                            <textarea class="mt-2 w-full font-bold text-slate-900" name="issue_description" rows="3" placeholder="Explain the defect or problem..." required></textarea>
                                        </label>
                                        <div class="flex flex-col gap-6 md:flex-row md:items-end">
                                            <label class="flex-1 group">
                                                <span class="text-slate-700">Evidence Image</span>
                                                <input class="mt-2 w-full font-bold text-slate-900" name="evidence_image" type="file" accept="image/*" required />
                                            </label>
                                            <button class="btn-primary bg-amber-600 hover:bg-amber-700 shadow-amber-600/20 px-10" type="submit">Submit Request</button>
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
                <h3 class="mb-6 text-xl font-black text-slate-900 uppercase">
                    Timeline
                </h3>

                <div class="space-y-8 relative">
                    <div class="absolute left-1.5 top-2 bottom-2 w-0.5 bg-slate-200"></div>

                    {#each timelineEvents as event (event.key)}
                        <div class="relative pl-10">
                            <div class="absolute left-0 top-1.5 h-3.5 w-3.5 rounded-full border-2 border-white bg-blue-600"></div>

                            <p class="text-sm font-black text-slate-900 uppercase">
                                {event.title}
                            </p>
                            <p class="text-sm text-slate-600">
                                {event.detail}
                            </p>
                            <p class="text-[10px] text-slate-400">
                                {formatDate(event.timestamp)}
                            </p>
                        </div>
                    {/each}
                </div>
            </section>
        </div>
    </div>
</section>