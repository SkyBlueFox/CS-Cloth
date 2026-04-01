<script lang="ts">
	import { itemImageSrc } from '$lib/media';
	let { data, form } = $props();
	let selectedAddressId = $state('__init__');
	let reportQuestionId = $state<number | null>(null);
	let reportReason = $state('');

	$effect(() => {
		if (selectedAddressId === '__init__') {
			selectedAddressId = data.addresses.find((address) => address.is_default)?.id?.toString() ?? '';
		}
	});

	function openReport(questionId: number) {
		reportQuestionId = questionId;
		reportReason = '';
	}

	function closeReport() {
		reportQuestionId = null;
		reportReason = '';
	}
</script>

<section class="grid gap-8 xl:grid-cols-[1.1fr_0.9fr]">
	<!-- Item details and questions -->
	<div class="space-y-8">
		<div class="panel">
			{#if itemImageSrc(data.item)}
				<img
					class="mb-6 h-[26rem] w-full rounded-[1.8rem] object-cover"
					src={itemImageSrc(data.item) ?? undefined}
					alt={data.item.name}
				/>
			{/if}
			<p class="eyebrow">Item</p>
			<h1 class="mt-3 text-4xl font-semibold">{data.item.name}</h1>
			<p class="mt-4 text-slate-600">{data.item.description}</p>
			<div class="mt-6 flex flex-wrap gap-4 text-sm text-slate-500">
				<span class="rounded-full bg-sky-100 px-4 py-2 text-sky-900">฿{data.item.price.toFixed(2)}</span>
				<span class="rounded-full bg-sky-100 px-4 py-2 text-sky-900">{data.item.stock} in stock</span>
			</div>
		</div>

		<div class="panel space-y-4">
			<div>
				<p class="eyebrow">Questions</p>
				<h2 class="mt-2 text-2xl font-semibold">Customer discussion</h2>
			</div>
			{#if data.questions.length === 0}
				<p class="text-slate-500">No questions yet.</p>
			{:else}
				<div class="space-y-4">
					{#each data.questions as question (question.id)}
						<article class="rounded-3xl border border-slate-200 p-4">
							<p class="text-sm font-medium text-slate-700">{question.asker_name} asked</p>
							<p class="mt-2">{question.question_text}</p>
							{#if question.answer_text}
								<div class="mt-4 rounded-[1.25rem] bg-sky-50 p-4">
									<div class="flex items-start justify-between gap-4">
										<p class="text-sm font-medium text-slate-600">{question.admin_name ?? 'Admin'} answered</p>
										<button
											aria-label={question.is_reported_by_current_user ? 'Already reported' : 'Report answer'}
											class={`flex h-10 w-10 items-center justify-center rounded-full border transition ${
												question.is_reported_by_current_user
													? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400'
													: 'border-rose-200 bg-white text-rose-600 hover:bg-rose-50'
											}`}
											disabled={question.is_reported_by_current_user}
											title={question.is_reported_by_current_user ? "You've already flagged this reply" : 'Report this answer'}
											type="button"
											onclick={() => !question.is_reported_by_current_user && openReport(question.id)}
										>
											<svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 24 24">
												<path d="M5 4v16" />
												<path d="M5 5h10l-2 4 2 4H5" />
											</svg>
										</button>
									</div>
									<p class="mt-2">{question.answer_text}</p>
								</div>
							{:else}
								<p class="mt-3 text-sm text-amber-700">Pending admin answer.</p>
							{/if}
						</article>
					{/each}
				</div>
			{/if}
		</div>
	</div>

	<!-- Sidebar forms: order & question -->
	<div class="space-y-6">
		{#if form?.error}
			<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
		{/if}
		{#if form?.success}
			<p class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{form.success}</p>
		{/if}

		<form class="panel space-y-4" method="POST" action="?/order">
			<div>
				<p class="eyebrow">Buy</p>
				<h2 class="mt-2 text-2xl font-semibold">Place an order</h2>
			</div>
			<label class="block">
				<span class="mb-1 block text-sm font-medium">Quantity</span>
				<input class="w-full rounded-2xl border-slate-300" name="quantity" type="number" min="1" max={data.item.stock} value="1" />
			</label>
			{#if data.addresses.length > 0}
				<label class="block">
					<span class="mb-1 block text-sm font-medium">Saved address</span>
					<select bind:value={selectedAddressId} class="w-full rounded-2xl border-slate-300" name="address_id">
						<option value="">Enter a new address</option>
						{#each data.addresses as address (address.id)}
							<option value={address.id.toString()}>{address.label} · {address.formatted}</option>
						{/each}
					</select>
				</label>
			{/if}
			{#if data.addresses.length === 0 || selectedAddressId === ''}
				<div class="subtle-box grid gap-3 p-4">
					<p class="text-sm font-medium text-sky-900">New shipping address</p>
					<div class="grid gap-3 md:grid-cols-2">
						<input class="rounded-2xl border-slate-300" name="label" placeholder="Home, Dorm, Office" />
						<input class="rounded-2xl border-slate-300" name="recipient_name" placeholder="Recipient name" />
						<input class="rounded-2xl border-slate-300" name="phone" placeholder="Phone" />
						<input class="rounded-2xl border-slate-300" name="country" placeholder="Country" value="Thailand" />
						<input class="rounded-2xl border-slate-300 md:col-span-2" name="line_1" placeholder="Address line 1" />
						<input class="rounded-2xl border-slate-300 md:col-span-2" name="line_2" placeholder="Address line 2" />
						<input class="rounded-2xl border-slate-300" name="district" placeholder="District" />
						<input class="rounded-2xl border-slate-300" name="province" placeholder="Province" />
						<input class="rounded-2xl border-slate-300" name="postal_code" placeholder="Postal code" />
					</div>
					<label class="flex items-center gap-2 text-sm text-slate-700">
						<input name="save_address" type="checkbox" value="1" />
						Save this address to my profile
					</label>
					<label class="flex items-center gap-2 text-sm text-slate-700">
						<input name="set_as_default" type="checkbox" value="1" />
						Set as default
					</label>
				</div>
			{/if}
			<button class="btn-primary w-full" type="submit">Order now</button>
		</form>

		<form class="panel space-y-4" method="POST" action="?/question">
			<div>
				<p class="eyebrow">Ask</p>
				<h2 class="mt-2 text-2xl font-semibold">Submit a question</h2>
			</div>
			<textarea class="w-full rounded-2xl border-slate-300" name="question_text" rows="4" maxlength="255" required></textarea>
			<button class="btn-secondary w-full" type="submit">Ask admin</button>
		</form>
	</div>
</section>

{#if reportQuestionId !== null}
	<button
		aria-label="Close report dialog"
		class="fixed inset-0 z-40 bg-slate-950/35 backdrop-blur-[2px]"
		type="button"
		onclick={closeReport}
	></button>
	<div class="fixed inset-0 z-50 flex items-center justify-center p-4">
		<form class="panel w-full max-w-lg space-y-4" method="POST" action="?/report">
			<input name="question_id" type="hidden" value={reportQuestionId} />
			<div class="flex items-start justify-between gap-4">
				<div>
					<p class="eyebrow">Report Answer</p>
					<h3 class="mt-2 text-2xl font-semibold">Flag this admin response</h3>
				</div>
				<button class="btn-secondary flex h-10 w-10 items-center justify-center rounded-full p-0" type="button" onclick={closeReport}>
					×
				</button>
			</div>
			<p class="text-sm text-slate-600">Explain why this answer is inappropriate, inaccurate, or unhelpful.</p>
			<label class="block">
				<span class="mb-1 block text-sm font-medium text-slate-700">Reason</span>
				<textarea
					bind:value={reportReason}
					class="w-full rounded-2xl border-slate-300"
					name="reason"
					rows="4"
					minlength="10"
					maxlength="255"
					placeholder="Write your report reason here"
					required
				></textarea>
			</label>
			<div class="flex justify-end gap-3">
				<button class="btn-secondary" type="button" onclick={closeReport}>Cancel</button>
				<button class="btn-danger" type="submit">Submit report</button>
			</div>
		</form>
	</div>
{/if}
