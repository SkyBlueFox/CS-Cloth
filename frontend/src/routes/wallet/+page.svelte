<script lang="ts">
	import applePayLogo from '$lib/assets/payment-gateway/ApplePay.png';
	import americanExpressLogo from '$lib/assets/payment-gateway/AmericanExpress.png';
	import googlePayLogo from '$lib/assets/payment-gateway/GooglePay.png';
	import jcbLogo from '$lib/assets/payment-gateway/JCB.png';
	import linePayLogo from '$lib/assets/payment-gateway/LinePay.png';
	import mastercardLogo from '$lib/assets/payment-gateway/Mastercard.png';
	import shopeePayLogo from '$lib/assets/payment-gateway/ShopeePay.png';
	import trueMoneyLogo from '$lib/assets/payment-gateway/TrueMoney.png';
	import unionPayLogo from '$lib/assets/payment-gateway/UnionPay.png';
	import visaLogo from '$lib/assets/payment-gateway/Visa.png';
	import bangkokBankLogo from '$lib/assets/payment-gateway/mobile-banking/BangkokBank.png';
	import kPlusLogo from '$lib/assets/payment-gateway/mobile-banking/KPLUS.png';
	import krungsriLogo from '$lib/assets/payment-gateway/mobile-banking/Krungsri.png';
	import krungthaiNextLogo from '$lib/assets/payment-gateway/mobile-banking/KrungthaiNEXT.png';
	import scbEasyLogo from '$lib/assets/payment-gateway/mobile-banking/SCBEASY.png';

	let { data, form } = $props();

	const presetAmounts = [100, 300, 500, 1000, 2000];
	const providers = [
		{
			group: 'mobile_banking',
			value: 'scb_easy',
			label: 'SCB EASY',
			note: 'Instant confirmation via app',
			logo: scbEasyLogo
		},
		{
			group: 'mobile_banking',
			value: 'k_plus',
			label: 'K PLUS',
			note: 'Kasikorn Bank mobile app',
			logo: kPlusLogo
		},
		{
			group: 'mobile_banking',
			value: 'krungthai_next',
			label: 'Krungthai NEXT',
			note: 'Krungthai Bank mobile app',
			logo: krungthaiNextLogo
		},
		{
			group: 'mobile_banking',
			value: 'krungsri_app',
			label: 'Krungsri Mobile App',
			note: 'Krungsri mobile banking',
			logo: krungsriLogo
		},
		{
			group: 'mobile_banking',
			value: 'bualuang_mbanking',
			label: 'Bangkok Bank Mobile Banking',
			note: 'Bangkok Bank mobile app',
			logo: bangkokBankLogo
		},
		{
			group: 'wallet',
			value: 'line_pay',
			label: 'LINE Pay',
			note: 'Pay with your LINE Pay wallet',
			logo: linePayLogo
		},
		{
			group: 'wallet',
			value: 'apple_pay',
			label: 'Apple Pay',
			note: 'Pay with Apple Pay',
			logo: applePayLogo
		},
		{
			group: 'wallet',
			value: 'truemoney',
			label: 'TrueMoney',
			note: 'Pay with TrueMoney Wallet',
			logo: trueMoneyLogo
		},
		{
			group: 'wallet',
			value: 'google_pay',
			label: 'Google Pay',
			note: 'Pay with Google Pay',
			logo: googlePayLogo
		},
		{
			group: 'wallet',
			value: 'shopee_pay',
			label: 'ShopeePay',
			note: 'Pay with ShopeePay',
			logo: shopeePayLogo
		},
		{
			group: 'card',
			value: 'credit_debit_card',
			label: 'Credit / Debit Card',
			note: 'Pay using your credit or debit card details.',
			logo: visaLogo,
			extraLogos: [mastercardLogo, unionPayLogo, jcbLogo, americanExpressLogo]
		}
	];

	const mobileBankingProviders = providers.filter((provider) => provider.group === 'mobile_banking');
	const alternateProviders = providers.filter((provider) => provider.group !== 'mobile_banking');

	let selectedPresetAmount = $state('300');
	let customAmount = $state('');
	let selectedProvider = $state('scb_easy');
	let cardholderName = $state('');
	let cardNumber = $state('');
	let expiryDate = $state('');
	let cvv = $state('');

	function formatDate(value: string | null) {
		if (!value) return 'Pending';

		return new Intl.DateTimeFormat('th-TH', {
			dateStyle: 'medium',
			timeStyle: 'short'
		}).format(new Date(value));
	}

	function providerLabel(value: string | null) {
		return providers.find((provider) => provider.value === value)?.label ?? value ?? 'Other';
	}

	function selectPresetAmount(amount: number) {
		selectedPresetAmount = String(amount);
		customAmount = '';
	}

	function customAmountChanged(event: Event) {
		const input = event.currentTarget as HTMLInputElement;
		customAmount = input.value;

		if (customAmount !== '') {
			selectedPresetAmount = '';
		}
	}

	function cardNumberChanged(event: Event) {
		const input = event.currentTarget as HTMLInputElement;
		const digits = input.value.replace(/\D+/g, '').slice(0, 19);
		cardNumber = digits.replace(/(.{4})/g, '$1 ').trim();
	}

	function expiryDateChanged(event: Event) {
		const input = event.currentTarget as HTMLInputElement;
		const digits = input.value.replace(/\D+/g, '').slice(0, 4);
		expiryDate = digits.length > 2 ? `${digits.slice(0, 2)}/${digits.slice(2)}` : digits;
	}

	function cvvChanged(event: Event) {
		const input = event.currentTarget as HTMLInputElement;
		cvv = input.value.replace(/\D+/g, '').slice(0, 4);
	}

	$effect(() => {
		selectedPresetAmount = String(form?.values?.preset_amount ?? '300');
		customAmount = String(form?.values?.custom_amount ?? '');
		selectedProvider = String(form?.values?.provider ?? 'scb_easy');
		cardholderName = String(form?.values?.cardholder_name ?? '');
		cardNumber = String(form?.values?.card_number ?? '');
		expiryDate = String(form?.values?.expiry_date ?? '');
		cvv = String(form?.values?.cvv ?? '');
	});
</script>

<section class="mx-auto max-w-7xl space-y-8">
	<div class="panel flex flex-wrap items-center justify-between gap-8 border-none p-8 sm:p-10">
		<div class="space-y-2">
			<div class="flex items-center gap-2">
				<span class="h-1.5 w-8 rounded-full bg-blue-500"></span>
				<p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600">Digital Account</p>
			</div>
			<h1 class="text-4xl font-black tracking-tight text-slate-900">Your Wallet</h1>
			<p class="text-sm font-bold uppercase tracking-wide text-slate-500">Manage funds and view activity</p>
		</div>
		<div class="rounded-[2rem] bg-slate-50 p-8 text-right text-slate-900 ring-1 ring-slate-200">
			<p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600">Available Balance</p>
			<p class="mt-2 text-5xl font-black tracking-tighter text-slate-900">
				฿{data.balance.toLocaleString(undefined, { minimumFractionDigits: 2 })}
			</p>
		</div>
	</div>

	{#if form?.error || form?.success}
		<div class="rounded-[1.5rem] shadow-lg">
			{#if form?.error}
				<p class="bg-rose-50 px-8 py-5 text-sm font-black text-rose-800 ring-1 ring-rose-200">{form.error}</p>
			{/if}
			{#if form?.success}
				<p class="bg-emerald-50 px-8 py-5 text-sm font-black text-emerald-800 ring-1 ring-emerald-200">{form.success}</p>
			{/if}
		</div>
	{/if}

	<div class="grid gap-8 xl:grid-cols-[1.1fr_0.9fr]">
		<form class="panel space-y-10" method="POST" action="?/topUp">
			<div>
				<p class="eyebrow text-blue-600">Funding</p>
				<h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Top-up Balance</h2>
			</div>

			<div class="grid gap-4 sm:grid-cols-3">
				{#each presetAmounts as amount (amount)}
					<label class="group relative cursor-pointer">
						<input
							class="sr-only"
							name="preset_amount"
							type="radio"
							value={String(amount)}
							bind:group={selectedPresetAmount}
							onchange={() => selectPresetAmount(amount)}
						/>
						<div
							class={`flex flex-col items-center rounded-3xl border-2 p-6 transition-all ${
								selectedPresetAmount === String(amount)
									? 'border-blue-600 bg-blue-50 shadow-xl shadow-blue-600/10'
									: 'border-slate-100 bg-white hover:border-blue-200'
							}`}
						>
							<span
								class={`text-[10px] font-black uppercase tracking-widest ${
									selectedPresetAmount === String(amount) ? 'text-blue-600' : 'text-slate-400'
								}`}
							>
								Quick
							</span>
							<span class="mt-1 text-2xl font-black text-slate-900">฿{amount}</span>
						</div>
					</label>
				{/each}
			</div>

			<label class="block group">
				<span class="text-slate-700">Or Custom Amount (Minimum ฿1)</span>
				<div class="relative mt-2">
					<span class="absolute left-5 top-1/2 -translate-y-1/2 text-lg font-black text-slate-400">฿</span>
					<input
						class="w-full rounded-[1.5rem] py-5 pl-10 pr-6 text-xl font-black text-slate-900"
						name="custom_amount"
						type="number"
						bind:value={customAmount}
						oninput={customAmountChanged}
						placeholder="0.00"
					/>
				</div>
			</label>

			<div class="space-y-6">
				<div class="flex items-center justify-between border-b border-slate-100 pb-4">
					<h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-900">Mobile Banking</h3>
					<span class="rounded-full bg-slate-100 px-3 py-1 text-[9px] font-black uppercase text-slate-500">Instant top-up</span>
				</div>

				<div class="grid gap-3">
					{#each mobileBankingProviders as provider (provider.value)}
						<label class="group relative cursor-pointer">
							<input class="sr-only" name="provider" type="radio" value={provider.value} bind:group={selectedProvider} />
							<div
								class={`flex items-center gap-5 rounded-[1.5rem] border-2 p-5 transition-all ${
									selectedProvider === provider.value
										? 'border-blue-600 bg-blue-50/50 shadow-lg'
										: 'border-slate-50 bg-white hover:border-slate-200'
								}`}
							>
								<div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white p-2 shadow-sm ring-1 ring-slate-100">
									<img alt={provider.label} class="h-full w-full object-contain" src={provider.logo} />
								</div>
								<div class="flex-1">
									<p class="text-sm font-black uppercase tracking-tight text-slate-900">{provider.label}</p>
									<p class="text-xs font-bold text-slate-500">{provider.note}</p>
								</div>
								<div
									class={`h-6 w-6 rounded-full border-2 border-slate-200 group-hover:border-blue-300 ${
										selectedProvider === provider.value ? 'border-blue-600 bg-blue-600 ring-4 ring-blue-100' : ''
									}`}
								></div>
							</div>
						</label>
					{/each}
				</div>
			</div>

			<div class="space-y-6">
				<div class="flex items-center justify-between border-b border-slate-100 pb-4">
					<h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-900">Other Payment Methods</h3>
					<span class="rounded-full bg-slate-100 px-3 py-1 text-[9px] font-black uppercase text-slate-500">More ways to pay</span>
				</div>

				<div class="grid gap-3">
					{#each alternateProviders as provider (provider.value)}
						<label class="group relative cursor-pointer">
							<input class="sr-only" name="provider" type="radio" value={provider.value} bind:group={selectedProvider} />
							<div
								class={`flex items-start gap-5 rounded-[1.5rem] border-2 p-5 transition-all ${
									selectedProvider === provider.value
										? 'border-blue-600 bg-blue-50/50 shadow-lg'
										: 'border-slate-50 bg-white hover:border-slate-200'
								}`}
							>
								<div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white p-2 shadow-sm ring-1 ring-slate-100">
									<img alt={provider.label} class="max-h-full max-w-full object-contain" src={provider.logo} />
								</div>
								<div class="flex-1">
									<div class="flex flex-wrap items-center justify-between gap-2">
										<p class="text-sm font-black uppercase tracking-tight text-slate-900">{provider.label}</p>
										<div
											class={`h-6 w-6 rounded-full border-2 border-slate-200 group-hover:border-blue-300 ${
												selectedProvider === provider.value ? 'border-blue-600 bg-blue-600 ring-4 ring-blue-100' : ''
											}`}
										></div>
									</div>
									<p class="mt-1 text-xs font-bold text-slate-500">{provider.note}</p>
									{#if provider.extraLogos}
										<div class="mt-3 flex flex-wrap items-center gap-2">
											{#each provider.extraLogos as extraLogo}
												<div class="flex h-8 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5">
													<img alt="" class="max-h-full max-w-full object-contain" src={extraLogo} />
												</div>
											{/each}
										</div>
									{/if}
								</div>
							</div>
						</label>
					{/each}
				</div>
			</div>

			{#if selectedProvider === 'credit_debit_card'}
				<div class="grid gap-5 rounded-[2rem] border border-slate-200 bg-slate-50 p-6 shadow-sm ring-1 ring-slate-900/5">
					<div>
						<p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600">Card Details</p>
						<p class="mt-2 text-sm font-semibold text-slate-500">Enter the card information required to complete this payment.</p>
					</div>
					<label class="block col-span-2">
						<span class="text-slate-700">Cardholder Name</span>
						<input
							class="w-full rounded-2xl border-slate-200 bg-white text-slate-900 placeholder:text-slate-400"
							name="cardholder_name"
							bind:value={cardholderName}
							placeholder="JOHN DOE"
						/>
					</label>
					<label class="block col-span-2">
						<span class="text-slate-700">Card Number</span>
						<input
							class="w-full rounded-2xl border-slate-200 bg-white tracking-[0.2em] text-slate-900 placeholder:text-slate-400"
							name="card_number"
							bind:value={cardNumber}
							oninput={cardNumberChanged}
							placeholder="0000 0000 0000 0000"
						/>
					</label>
					<div class="grid grid-cols-2 gap-5">
						<label class="block">
							<span class="text-slate-700">Expiry Date</span>
							<input
								class="w-full rounded-2xl border-slate-200 bg-white text-center text-slate-900 placeholder:text-slate-400"
								name="expiry_date"
								bind:value={expiryDate}
								oninput={expiryDateChanged}
								placeholder="MM/YY"
							/>
						</label>
						<label class="block">
							<span class="text-slate-700">CVV</span>
							<input
								class="w-full rounded-2xl border-slate-200 bg-white text-center text-slate-900 placeholder:text-slate-400"
								name="cvv"
								bind:value={cvv}
								oninput={cvvChanged}
								placeholder="CVV"
							/>
						</label>
					</div>
				</div>
			{/if}

			<button class="btn-primary w-full py-6 text-base tracking-[0.3em]" type="submit">Confirm Top-up</button>
		</form>

		<div class="space-y-6">
			<div class="panel h-full flex flex-col">
				<header class="mb-10">
					<p class="eyebrow text-slate-500">Ledger</p>
					<h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Recent Activity</h2>
				</header>

				{#if data.transactions.data.length === 0}
					<div class="flex flex-1 flex-col items-center justify-center rounded-[2.5rem] border-2 border-dashed border-slate-100 py-20 text-center">
						<p class="text-sm font-black uppercase tracking-widest text-slate-400">No activity yet</p>
					</div>
				{:else}
					<div class="space-y-4">
						{#each data.transactions.data as transaction (transaction.id)}
							<div class="rounded-3xl border border-slate-100 bg-white p-6 transition-all hover:border-blue-100 hover:shadow-xl hover:shadow-blue-900/5">
								<div class="flex items-start justify-between gap-4">
									<div class="flex items-center gap-4">
										<div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-900 shadow-inner">
											<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4" /></svg>
										</div>
										<div>
											<p class="text-sm font-black uppercase tracking-tight text-slate-900">{transaction.type.replace('_', ' ')}</p>
											<p class="text-[10px] font-bold uppercase text-slate-400">{providerLabel(transaction.provider)}</p>
										</div>
									</div>
									<div class="text-right">
										<p class="text-lg font-black text-emerald-600">+฿{transaction.amount.toLocaleString()}</p>
										<p class="text-[9px] font-black uppercase tracking-widest text-slate-400">{formatDate(transaction.completed_at)}</p>
									</div>
								</div>
								<div class="mt-6 flex justify-between border-t border-slate-50 pt-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">
									<span>Balance: ฿{transaction.balance_before.toLocaleString()}</span>
									<span class="text-blue-600">→ ฿{transaction.balance_after.toLocaleString()}</span>
								</div>
							</div>
						{/each}
					</div>
				{/if}
			</div>
		</div>
	</div>
</section>
