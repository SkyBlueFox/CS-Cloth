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
			note: 'Pay through the SCB EASY app simulation.',
			logo: scbEasyLogo
		},
		{
			group: 'mobile_banking',
			value: 'k_plus',
			label: 'K PLUS',
			note: 'Pay through the K PLUS app simulation.',
			logo: kPlusLogo
		},
		{
			group: 'mobile_banking',
			value: 'krungthai_next',
			label: 'Krungthai NEXT',
			note: 'Pay through the Krungthai NEXT app simulation.',
			logo: krungthaiNextLogo
		},
		{
			group: 'mobile_banking',
			value: 'krungsri_app',
			label: 'Krungsri Mobile App',
			note: 'Pay through the Krungsri app simulation.',
			logo: krungsriLogo
		},
		{
			group: 'mobile_banking',
			value: 'bualuang_mbanking',
			label: 'Bangkok Bank Mobile Banking',
			note: 'Pay through the Bangkok Bank app simulation.',
			logo: bangkokBankLogo
		},
		{
			group: 'wallet',
			value: 'line_pay',
			label: 'LINE Pay',
			note: 'Simulate confirming the payment in LINE Pay.',
			logo: linePayLogo
		},
		{
			group: 'wallet',
			value: 'apple_pay',
			label: 'Apple Pay',
			note: 'Simulate confirming the payment with Apple Pay.',
			logo: applePayLogo
		},
		{
			group: 'wallet',
			value: 'truemoney',
			label: 'TrueMoney',
			note: 'Simulate confirming the payment in TrueMoney Wallet.',
			logo: trueMoneyLogo
		},
		{
			group: 'wallet',
			value: 'google_pay',
			label: 'Google Pay',
			note: 'Simulate confirming the payment with Google Pay.',
			logo: googlePayLogo
		},
		{
			group: 'wallet',
			value: 'shopee_pay',
			label: 'ShopeePay',
			note: 'Simulate confirming the payment in ShopeePay.',
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
	let selectedPresetAmount = $state('300');
	let customAmount = $state('');
	let selectedProvider = $state('scb_easy');
	let cardholderName = $state('');
	let cardNumber = $state('');
	let expiryDate = $state('');
	let cvv = $state('');

	function formatDate(value: string | null) {
		if (!value) return 'Not recorded';

		return new Intl.DateTimeFormat('en-GB', {
			dateStyle: 'medium',
			timeStyle: 'short'
		}).format(new Date(value));
	}

	function providerLabel(value: string | null) {
		return providers.find((provider) => provider.value === value)?.label ?? value ?? 'Unknown method';
	}

	const mobileBankingProviders = providers.filter((provider) => provider.group === 'mobile_banking');
	const alternateProviders = providers.filter((provider) => provider.group !== 'mobile_banking');

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

<section class="space-y-6">
	<div class="panel flex flex-wrap items-start justify-between gap-4">
		<div>
			<p class="eyebrow">Wallet</p>
			<h1 class="mt-2 text-3xl font-semibold">Top up your balance</h1>
			<p class="mt-3 max-w-2xl text-sm text-slate-600">
				Add funds to your wallet using mobile banking, e-wallet, or card payment options.
			</p>
		</div>
		<div class="rounded-[1.5rem] border border-emerald-200 bg-emerald-50 px-6 py-5 text-right">
			<p class="text-sm font-medium uppercase tracking-[0.18em] text-emerald-700">Current balance</p>
			<p class="mt-2 text-4xl font-semibold text-emerald-900">฿{data.balance.toFixed(2)}</p>
		</div>
	</div>

	{#if form?.error}
		<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
	{/if}
	{#if form?.success}
		<p class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{form.success}</p>
	{/if}

	<div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
		<form class="panel space-y-6" method="POST" action="?/topUp">
			<div>
				<p class="eyebrow">Mobile Banking</p>
				<h2 class="mt-2 text-2xl font-semibold">Choose amount and bank app</h2>
				<p class="mt-2 text-sm text-slate-600">
					Choose the amount first, then pick the payment method you want to use.
				</p>
			</div>

			<div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
				{#each presetAmounts as amount}
					<label
						class={`cursor-pointer rounded-[1.35rem] border p-4 transition ${
							selectedPresetAmount === String(amount)
								? 'border-blue-500 bg-blue-50 shadow-[0_0_0_1px_rgba(59,130,246,0.25)]'
								: 'border-slate-200 bg-white hover:border-blue-300 hover:bg-blue-50/50'
						}`}
					>
						<input
							class="sr-only"
							bind:group={selectedPresetAmount}
							name="preset_amount"
							onchange={() => selectPresetAmount(amount)}
							type="radio"
							value={String(amount)}
						/>
						<span class={`text-sm ${selectedPresetAmount === String(amount) ? 'text-blue-700' : 'text-slate-500'}`}>Quick top-up</span>
						<span class="mt-1 block text-2xl font-semibold text-slate-900">฿{amount}</span>
						<span class={`mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${
							selectedPresetAmount === String(amount)
								? 'bg-blue-600 text-white'
								: 'bg-slate-100 text-slate-500'
						}`}>
							{selectedPresetAmount === String(amount) ? 'Selected' : 'Tap to select'}
						</span>
					</label>
				{/each}
			</div>

			<label class="block">
				<span class="mb-1 block text-sm font-medium text-slate-700">Or enter a custom amount</span>
				<input
					class="w-full rounded-2xl border-slate-300"
					inputmode="decimal"
					min="50"
					max="50000"
					name="custom_amount"
					oninput={customAmountChanged}
					placeholder="50 - 50000"
					type="number"
					bind:value={customAmount}
				/>
			</label>

			<div class="space-y-3">
				<div class="flex items-center justify-between gap-3">
					<div>
						<p class="text-sm font-medium text-slate-700">Mobile banking app</p>
						<p class="mt-1 text-xs text-slate-500">Choose your preferred banking app.</p>
					</div>
					<div class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
						Instant top-up
					</div>
				</div>
				<div class="grid gap-3">
					{#each mobileBankingProviders as provider}
						<label
							class={`cursor-pointer rounded-[1.35rem] border p-4 transition ${
								selectedProvider === provider.value
									? 'border-blue-500 bg-blue-50 shadow-[0_0_0_1px_rgba(59,130,246,0.25)]'
									: 'border-slate-200 bg-white hover:border-blue-300 hover:bg-blue-50/50'
							}`}
						>
							<div class="flex items-start gap-3">
								<input
									class="mt-1"
									bind:group={selectedProvider}
									name="provider"
									type="radio"
									value={provider.value}
								/>
								<div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white p-2">
									<img alt={provider.label} class="h-full w-full object-contain" src={provider.logo} />
								</div>
								<div class="flex-1">
									<div class="flex flex-wrap items-center justify-between gap-2">
										<p class="font-medium text-slate-900">{provider.label}</p>
										<span class={`rounded-full px-2.5 py-1 text-xs font-medium ${
											selectedProvider === provider.value
												? 'bg-blue-600 text-white'
												: 'bg-slate-100 text-slate-500'
										}`}>
											{selectedProvider === provider.value ? 'Selected' : 'Choose'}
										</span>
									</div>
									<p class="mt-1 text-sm text-slate-500">{provider.note}</p>
								</div>
							</div>
						</label>
					{/each}
				</div>
			</div>

			<div class="space-y-3">
				<div class="flex items-center justify-between gap-3">
					<div>
						<p class="text-sm font-medium text-slate-700">Other payment methods</p>
						<p class="mt-1 text-xs text-slate-500">Alternative top-up options.</p>
					</div>
					<div class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
						More ways to pay
					</div>
				</div>
				<div class="grid gap-3">
					{#each alternateProviders as provider}
						<label
							class={`cursor-pointer rounded-[1.35rem] border p-4 transition ${
								selectedProvider === provider.value
									? 'border-blue-500 bg-blue-50 shadow-[0_0_0_1px_rgba(59,130,246,0.25)]'
									: 'border-slate-200 bg-white hover:border-blue-300 hover:bg-blue-50/50'
							}`}
						>
							<div class="flex items-start gap-3">
								<input
									class="mt-1"
									bind:group={selectedProvider}
									name="provider"
									type="radio"
									value={provider.value}
								/>
								<div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white p-2">
									<img alt={provider.label} class="max-h-full max-w-full object-contain" src={provider.logo} />
								</div>
								<div class="flex-1">
									<div class="flex flex-wrap items-center justify-between gap-2">
										<p class="font-medium text-slate-900">{provider.label}</p>
										<span class={`rounded-full px-2.5 py-1 text-xs font-medium ${
											selectedProvider === provider.value
												? 'bg-blue-600 text-white'
												: 'bg-slate-100 text-slate-500'
										}`}>
											{selectedProvider === provider.value ? 'Selected' : 'Choose'}
										</span>
									</div>
									<p class="mt-1 text-sm text-slate-500">{provider.note}</p>
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
				<div class="grid gap-4 rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4 md:grid-cols-2">
					<label class="block md:col-span-2">
						<span class="mb-1 block text-sm font-medium text-slate-700">Cardholder name</span>
						<input
							class="w-full rounded-2xl border-slate-300"
							name="cardholder_name"
							placeholder="Name on card"
							type="text"
							bind:value={cardholderName}
						/>
					</label>
					<label class="block md:col-span-2">
						<span class="mb-1 block text-sm font-medium text-slate-700">Card number</span>
						<input
							class="w-full rounded-2xl border-slate-300"
							name="card_number"
							inputmode="numeric"
							maxlength="23"
							oninput={cardNumberChanged}
							placeholder="1234 5678 9012 3456"
							type="text"
							bind:value={cardNumber}
						/>
					</label>
					<label class="block">
						<span class="mb-1 block text-sm font-medium text-slate-700">Expiry date</span>
						<input
							class="w-full rounded-2xl border-slate-300"
							name="expiry_date"
							inputmode="numeric"
							maxlength="5"
							oninput={expiryDateChanged}
							placeholder="MM/YY"
							type="text"
							bind:value={expiryDate}
						/>
					</label>
					<label class="block">
						<span class="mb-1 block text-sm font-medium text-slate-700">Security code</span>
						<input
							class="w-full rounded-2xl border-slate-300"
							name="cvv"
							inputmode="numeric"
							maxlength="4"
							oninput={cvvChanged}
							placeholder="CVV"
							type="text"
							bind:value={cvv}
						/>
					</label>
				</div>
			{/if}

			<button class="btn-primary rounded-full px-6 py-3 text-sm" type="submit">Confirm top-up</button>
		</form>

		<div class="panel space-y-5">
			<div>
				<p class="eyebrow">History</p>
				<h2 class="mt-2 text-2xl font-semibold">Recent wallet transactions</h2>
			</div>

			{#if data.transactions.data.length === 0}
				<div class="rounded-[1.5rem] border border-dashed border-slate-300 px-6 py-10 text-center text-sm text-slate-500">
					No wallet transactions yet.
				</div>
			{:else}
				<div class="space-y-3">
					{#each data.transactions.data as transaction (transaction.id)}
						<div class="rounded-[1.35rem] border border-slate-200 bg-white p-4">
							<div class="flex flex-wrap items-start justify-between gap-3">
								<div>
									<p class="font-medium text-slate-900">
										{transaction.type === 'top_up' ? 'Wallet top-up' : transaction.type}
									</p>
									<p class="mt-1 text-sm text-slate-500">
										{providerLabel(transaction.provider)} · {transaction.reference}
									</p>
								</div>
								<div class="text-right">
									<p class="text-lg font-semibold text-emerald-700">+฿{transaction.amount.toFixed(2)}</p>
									<p class="text-xs uppercase tracking-[0.14em] text-slate-500">{transaction.status}</p>
								</div>
							</div>
							<div class="mt-3 grid gap-1 text-sm text-slate-600">
								<p>Balance before: ฿{transaction.balance_before.toFixed(2)}</p>
								<p>Balance after: ฿{transaction.balance_after.toFixed(2)}</p>
								<p>Completed: {formatDate(transaction.completed_at)}</p>
							</div>
						</div>
					{/each}
				</div>
			{/if}
		</div>
	</div>
</section>
