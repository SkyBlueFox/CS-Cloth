<script lang="ts">
	import { itemImageSrc } from '$lib/media';
	import { deliveryOptions } from '$lib/delivery';
	import { fly, fade } from 'svelte/transition';
	let { data, form = $bindable() } = $props();
	let selectedAddressId = $state('__init__');
	let selectedDeliveryMethod = $state('thailand_post');
	let reportQuestionId = $state<number | null>(null);
	let reportReason = $state('');
	const usingNewAddress = $derived(selectedAddressId === '');

	$effect(() => {
		if (selectedAddressId === '__init__') {
			selectedAddressId = data.addresses.find((address) => address.is_default)?.id?.toString() ?? '';
		}
	});

	$effect(() => {
		if (!deliveryOptions.some((option) => option.value === selectedDeliveryMethod)) {
			selectedDeliveryMethod = deliveryOptions[0]?.value ?? 'thailand_post';
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

<section class="grid gap-12 lg:grid-cols-[1.2fr_1fr] xl:gap-16">
	<div class="space-y-12">
		<article class="relative overflow-hidden rounded-[3rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-12">
			{#if itemImageSrc(data.item)}
				<div class="mb-12 overflow-hidden rounded-[2.5rem] bg-slate-50 shadow-inner">
					<img
						class="h-[36rem] w-full object-cover transition-transform duration-1000 hover:scale-105"
						src={itemImageSrc(data.item) ?? undefined}
						alt={data.item.name}
					/>
				</div>
			{/if}

			<div class="flex items-center gap-3">
				<span class="h-1.5 w-10 rounded-full bg-blue-600"></span>
				<p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600">Premium Product</p>
			</div>

			<h1 class="mt-6 text-4xl font-black tracking-tight text-slate-900 sm:text-6xl">{data.item.name}</h1>
			<p class="mt-6 text-lg font-bold uppercase tracking-tight leading-relaxed text-slate-700">{data.item.description}</p>

			<div class="mt-10 flex flex-wrap items-center gap-6 border-t border-slate-50 pt-10">
				<div class="flex items-baseline gap-2">
					<span class="text-sm font-black text-slate-400">฿</span>
					<span class="text-5xl font-black tracking-tighter text-slate-900">
						{data.item.price.toLocaleString(undefined, { minimumFractionDigits: 2 })}
					</span>
				</div>
				<div class="h-10 w-px bg-slate-200"></div>
				<span class="rounded-full bg-blue-50 px-6 py-2 text-xs font-black uppercase tracking-widest text-blue-700 ring-1 ring-blue-100">
					{data.item.stock} Available in stock
				</span>
			</div>
		</article>

		{#if form?.error || form?.success}
			<div in:fly={{ y: -10 }} class="overflow-hidden rounded-[2rem] shadow-lg">
				{#if form?.error}
					<p class="bg-rose-50 px-8 py-5 text-sm font-black text-rose-800 ring-1 ring-rose-200 ring-inset">
						{form.error}
					</p>
				{:else if form?.success}
					<p class="bg-emerald-50 px-8 py-5 text-sm font-black text-emerald-800 ring-1 ring-emerald-200 ring-inset">
						{form.success}
					</p>
				{/if}
			</div>
		{/if}

		<section class="rounded-[3rem] border border-slate-100 bg-white p-8 shadow-sm sm:p-12">
			<header class="mb-10 flex items-center justify-between">
				<div>
					<p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Q&A Archive</p>
					<h2 class="mt-2 text-3xl font-black text-slate-900">Discussion</h2>
				</div>
			</header>

			{#if data.questions.length === 0}
				<div class="flex flex-col items-center justify-center rounded-[2.5rem] border-2 border-dashed border-slate-100 bg-slate-50 py-16 text-center">
					<p class="text-sm font-black uppercase tracking-widest text-slate-400">No active discussions</p>
				</div>
			{:else}
				<div class="space-y-8">
					{#each data.questions as question (question.id)}
						<article class="relative overflow-hidden rounded-[2.5rem] bg-slate-50/50 p-8 ring-1 ring-slate-100 transition-all hover:bg-slate-50">
							<div class="flex items-start gap-5">
								<div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white font-black text-slate-900 shadow-sm ring-1 ring-slate-200">
									{question.asker_name.charAt(0).toUpperCase()}
								</div>
								<div>
									<p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{question.asker_name} asked:</p>
									<p class="mt-2 text-base font-bold leading-relaxed text-slate-900">{question.question_text}</p>
								</div>
							</div>

							{#if question.answer_text}
								<div class="mt-8 ml-10 rounded-[2rem] bg-blue-600 p-7 text-white shadow-xl shadow-blue-600/20 ring-1 ring-blue-700">
									<div class="mb-4 flex items-center justify-between">
										<div class="flex items-center gap-3">
											<div class="h-1.5 w-1.5 rounded-full bg-blue-300"></div>
											<p class="text-[10px] font-black uppercase tracking-widest text-blue-100">Staff Response by {question.admin_name}</p>
										</div>
										{#if data.viewerRole === 'user'}
											<button
												class="text-white/50 transition-colors hover:text-white"
												onclick={() => !question.is_reported_by_current_user && openReport(question.id)}
												title="Report Answer"
											>
												<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 4v16M5 5h10l-2 4 2 4H5" /></svg>
											</button>
										{/if}
									</div>
									<p class="text-sm font-black leading-relaxed">{question.answer_text}</p>
								</div>
							{:else}
								<div class="mt-6 ml-10 flex items-center gap-3">
									<span class="h-2 w-2 animate-ping rounded-full bg-amber-400"></span>
									<p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-600">Reviewing In Progress</p>
								</div>
							{/if}
						</article>
					{/each}
				</div>
			{/if}
		</section>
	</div>

	<div class="space-y-8">
		{#if data.viewerRole === 'user'}
			<form class="sticky top-24 rounded-[3rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-10" method="POST" action="?/order">
				<header class="mb-8 border-b border-slate-100 pb-8">
					<div class="flex items-center gap-3">
						<span class="h-1.5 w-10 rounded-full bg-blue-600"></span>
						<p class="text-[10px] font-black uppercase tracking-[0.35em] text-blue-600">Checkout</p>
					</div>
					<h2 class="mt-4 text-3xl font-black tracking-tight text-slate-900">Order This Item</h2>
					<p class="mt-3 text-sm font-bold uppercase tracking-wide text-slate-500">
						Choose quantity, delivery service, and shipping address.
					</p>
				</header>

				<div class="space-y-8">
					<div class="rounded-[2rem] bg-slate-50 p-6 ring-1 ring-slate-100">
						<div class="flex items-start gap-4">
							<div class="h-20 w-20 shrink-0 overflow-hidden rounded-[1.5rem] bg-white ring-1 ring-slate-200">
								{#if itemImageSrc(data.item)}
									<img class="h-full w-full object-cover" src={itemImageSrc(data.item) ?? undefined} alt={data.item.name} />
								{/if}
							</div>
							<div class="min-w-0 flex-1">
								<p class="truncate text-lg font-black text-slate-900">{data.item.name}</p>
								<p class="mt-1 text-[11px] font-black uppercase tracking-widest text-slate-400">Unit price</p>
								<p class="mt-2 text-2xl font-black tracking-tight text-blue-700">
									฿{data.item.price.toLocaleString(undefined, { minimumFractionDigits: 2 })}
								</p>
							</div>
						</div>
					</div>

					<label class="block group">
						<span class="mb-3 block text-[10px] font-black uppercase tracking-widest text-slate-500 transition-colors group-focus-within:text-blue-600">Order Quantity</span>
						<input class="w-full rounded-2xl border border-slate-200 bg-white px-6 py-4 text-lg font-black text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" name="quantity" type="number" min="1" max={data.item.stock} value="1" />
					</label>

					<div>
						<div class="mb-3 flex items-center justify-between gap-4">
							<span class="block text-[10px] font-black uppercase tracking-widest text-slate-500">Delivery Option</span>
							<span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pick one</span>
						</div>
						<div class="grid gap-3 sm:grid-cols-2">
							{#each deliveryOptions as option (option.value)}
								<button
									type="button"
									class={`flex items-center gap-4 rounded-[1.75rem] border p-4 text-left transition-all ${
										selectedDeliveryMethod === option.value
											? 'border-blue-500 bg-blue-50 shadow-lg shadow-blue-500/10'
											: 'border-slate-200 bg-white hover:border-blue-200 hover:bg-slate-50'
									}`}
									onclick={() => (selectedDeliveryMethod = option.value)}
								>
									<div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.25rem] bg-white p-2 ring-1 ring-slate-200">
										<img alt={option.label} class="max-h-full max-w-full object-contain" src={option.logo} />
									</div>
									<div class="min-w-0">
										<p class="text-sm font-black uppercase tracking-tight text-slate-900">{option.label}</p>
										<p class="mt-1 text-xs font-bold leading-relaxed text-slate-500">{option.note}</p>
									</div>
								</button>
							{/each}
						</div>
						<input type="hidden" name="delivery_method" value={selectedDeliveryMethod} />
					</div>

					<label class="block group">
						<span class="mb-3 block text-[10px] font-black uppercase tracking-widest text-slate-500 transition-colors group-focus-within:text-blue-600">Ship to</span>
						<select class="w-full rounded-2xl border border-slate-200 bg-white px-6 py-4 text-sm font-bold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" name="address_id" bind:value={selectedAddressId}>
							{#each data.addresses as address (address.id)}
								<option value={address.id.toString()}>{address.label} · {address.province}</option>
							{/each}
							<option value="">+ Use New Address</option>
						</select>
					</label>

					{#if usingNewAddress}
						<div class="space-y-6 rounded-[2rem] border border-slate-200 bg-slate-50 p-6">
							<div class="flex items-center gap-3">
								<span class="h-1.5 w-8 rounded-full bg-blue-600"></span>
								<p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600">New Address</p>
							</div>

							<div class="grid gap-4 sm:grid-cols-2">
								<label class="block">
									<span class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Address Label</span>
									<input class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm font-bold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" name="label" type="text" placeholder="Home, Condo, Office" />
								</label>
								<label class="block">
									<span class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Recipient Name</span>
									<input class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm font-bold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" name="recipient_name" type="text" placeholder="Full name" />
								</label>
							</div>

							<div class="grid gap-4 sm:grid-cols-2">
								<label class="block">
									<span class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Phone</span>
									<input class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm font-bold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" name="phone" type="tel" placeholder="08X-XXX-XXXX" />
								</label>
								<label class="block">
									<span class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Country</span>
									<input class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm font-bold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" name="country" type="text" value="Thailand" />
								</label>
							</div>

							<label class="block">
								<span class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Address Line 1</span>
								<input class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm font-bold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" name="line_1" type="text" placeholder="House number, street, building" />
							</label>

							<label class="block">
								<span class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Address Line 2</span>
								<input class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm font-bold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" name="line_2" type="text" placeholder="Apartment, floor, landmark (optional)" />
							</label>

							<div class="grid gap-4 sm:grid-cols-3">
								<label class="block">
									<span class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">District</span>
									<input class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm font-bold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" name="district" type="text" placeholder="District" />
								</label>
								<label class="block">
									<span class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Province</span>
									<input class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm font-bold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" name="province" type="text" placeholder="Province" />
								</label>
								<label class="block">
									<span class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Postal Code</span>
									<input class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm font-bold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" name="postal_code" type="text" inputmode="numeric" placeholder="10110" />
								</label>
							</div>

							<div class="space-y-3 rounded-[1.5rem] bg-white p-5 ring-1 ring-slate-200">
								<label class="flex items-start gap-3">
									<input class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" name="save_address" type="checkbox" value="1" />
									<div>
										<p class="text-sm font-black text-slate-900">Save this address</p>
										<p class="mt-1 text-xs font-bold text-slate-500">Keep it available for future orders.</p>
									</div>
								</label>
								<label class="flex items-start gap-3">
									<input class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" name="set_as_default" type="checkbox" value="1" />
									<div>
										<p class="text-sm font-black text-slate-900">Set as default</p>
										<p class="mt-1 text-xs font-bold text-slate-500">Use this as your default shipping address next time.</p>
									</div>
								</label>
							</div>
						</div>
					{/if}

					<button class="w-full rounded-2xl bg-slate-900 py-5 text-sm font-black uppercase tracking-[0.25em] text-white shadow-xl shadow-slate-900/15 transition-all hover:-translate-y-0.5 hover:bg-blue-600 active:translate-y-0" type="submit">
						Complete Purchase
					</button>
				</div>
			</form>

			<form class="rounded-[2.5rem] bg-white p-8 shadow-xl shadow-slate-200/50 ring-1 ring-slate-100" method="POST" action="?/question">
				<h3 class="text-lg font-black uppercase tracking-tight text-slate-900">Got Questions?</h3>
				<textarea class="mt-6 w-full resize-none rounded-2xl border-slate-100 bg-slate-50 p-5 text-sm font-bold text-slate-900 placeholder:text-slate-300 focus:border-blue-500 focus:ring-0" name="question_text" rows="3" placeholder="Inquire about sizing, materials or restock..."></textarea>
				<button class="mt-4 w-full rounded-xl py-3 text-xs font-black uppercase tracking-widest text-slate-400 transition-colors hover:text-blue-600">Send Inquiry</button>
			</form>
		{:else}
			<div class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm">
				<h2 class="text-2xl font-black tracking-tight text-slate-900">
					{data.viewerRole ? 'Storefront unavailable for this account' : 'Sign in to purchase'}
				</h2>
				<p class="mt-3 text-sm font-bold uppercase tracking-wide text-slate-500">
					{data.viewerRole
						? 'Admin and superadmin accounts cannot place customer orders or manage shipping addresses here.'
						: 'Customer checkout, address entry, and questions are only available after logging in to a user account.'}
				</p>
				<a class="mt-6 inline-flex rounded-2xl bg-slate-900 px-6 py-4 text-sm font-black uppercase tracking-[0.2em] text-white transition hover:bg-blue-600" href={data.viewerRole ? '/' : '/login'}>
					{data.viewerRole ? 'Back to dashboard' : 'Login'}
				</a>
			</div>
		{/if}
	</div>
</section>

{#if reportQuestionId !== null}
	<div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" in:fade={{ duration: 200 }} out:fade={{ duration: 150 }}>
		<button
			aria-label="Close dialog"
			class="absolute inset-0 h-full w-full cursor-default bg-slate-900/60 backdrop-blur-md transition-opacity"
			type="button"
			onclick={closeReport}
		></button>

		<form
			class="relative w-full max-w-lg overflow-hidden rounded-[3rem] bg-white p-10 shadow-2xl ring-1 ring-slate-900/5"
			method="POST"
			action="?/report"
			in:fly={{ y: 20, duration: 300 }}
		>
			<input name="question_id" type="hidden" value={reportQuestionId} />

			<div class="mb-8">
				<div class="mb-6 flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
						<path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd" />
					</svg>
				</div>
				<h3 class="text-3xl font-black tracking-tight text-slate-900">Report Answer</h3>
				<p class="mt-3 text-sm font-bold uppercase tracking-wide text-slate-500">Tell us why this response is inappropriate.</p>
			</div>

			<label class="block">
				<textarea
					bind:value={reportReason}
					class="w-full resize-none rounded-2xl border-slate-200 bg-slate-50 p-6 text-sm font-black text-slate-900 transition-all placeholder:text-slate-400 focus:border-rose-400 focus:bg-white focus:ring-4 focus:ring-rose-400/10"
					name="reason"
					rows="4"
					minlength="10"
					maxlength="255"
					placeholder="Provide specific details here..."
					required
				></textarea>
			</label>

			<div class="mt-10 flex flex-col-reverse justify-end gap-4 sm:flex-row">
				<button class="w-full rounded-2xl px-6 py-4 text-xs font-black uppercase tracking-widest text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-900 sm:w-auto" type="button" onclick={closeReport}>
					Cancel
				</button>
				<button class="w-full rounded-2xl bg-rose-600 px-8 py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-rose-600/20 transition-all hover:bg-rose-700 active:scale-95 sm:w-auto" type="submit">
					Send Report
				</button>
			</div>
		</form>
	</div>
{/if}
