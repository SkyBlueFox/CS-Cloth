<script lang="ts">
    import { fly, fade } from 'svelte/transition';

    let { data, form } = $props();

    const presetAmounts = [100, 300, 500, 1000, 2000];
    
    const providers = [
        { 
            value: 'scb_easy', 
            label: 'SCB EASY', 
            note: 'Instant confirmation via app', 
            group: 'mobile_banking',
            logo: 'https://www.scb.co.th/content/dam/scb/personal-banking/digital-banking/scb-easy/app-icon/scbeasy-icon.png'
        },
        { 
            value: 'k_plus', 
            label: 'K PLUS', 
            note: 'Kasikorn Bank mobile app', 
            group: 'mobile_banking',
            logo: 'https://kasikornbank.com/SiteCollectionImages/digital-banking/kplus/logo/logo-kplus.png'
        },
        { 
            value: 'credit_debit_card', 
            label: 'Credit / Debit Card', 
            note: 'Visa, Mastercard, JCB', 
            group: 'card',
            logo: 'https://cdn-icons-png.flaticon.com/512/349/349221.png'
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
        if (!value) return 'Pending';
        return new Intl.DateTimeFormat('th-TH', {
            dateStyle: 'medium',
            timeStyle: 'short'
        }).format(new Date(value));
    }

    function providerLabel(value: string | null) {
        return providers.find((p) => p.value === value)?.label ?? value ?? 'Other';
    }

    function customAmountChanged() {
        selectedPresetAmount = '';
    }

    function cardNumberChanged(e: Event) {
        const input = e.target as HTMLInputElement;
        cardNumber = input.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim().slice(0, 19);
    }

    function expiryDateChanged(e: Event) {
        const input = e.target as HTMLInputElement;
        expiryDate = input.value.replace(/\D/g, '').replace(/(.{2})/, '$1/').trim().slice(0, 5);
    }

    function cvvChanged(e: Event) {
        cvv = (e.target as HTMLInputElement).value.replace(/\D/g, '').slice(0, 3);
    }
</script>

<section class="mx-auto max-w-7xl space-y-8">
    <div class="panel flex flex-wrap items-center justify-between gap-8 border-none bg-slate-900 text-white shadow-2xl">
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <span class="h-1.5 w-8 rounded-full bg-blue-500"></span>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-400">Digital Account</p>
            </div>
            <h1 class="text-4xl font-black tracking-tight">Your Wallet</h1>
            <p class="text-sm font-bold text-slate-400 uppercase tracking-wide">Manage funds and view activity</p>
        </div>
        <div class="rounded-[2rem] bg-white/10 p-8 text-right backdrop-blur-md ring-1 ring-white/20">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-300">Available Balance</p>
            <p class="mt-2 text-5xl font-black tracking-tighter text-white">
                ฿{data.balance.toLocaleString(undefined, { minimumFractionDigits: 2 })}
            </p>
        </div>
    </div>

    {#if form?.error || form?.success}
        <div in:fly={{ y: -10 }} class="rounded-[1.5rem] shadow-lg">
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
                <h2 class="mt-2 text-3xl font-black text-slate-900 tracking-tight">Top-up Balance</h2>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                {#each presetAmounts as amount (amount)}
                    <label class="group relative cursor-pointer">
                        <input class="sr-only" name="preset_amount" type="radio" value={String(amount)} bind:group={selectedPresetAmount} />
                        <div class="flex flex-col items-center rounded-3xl border-2 p-6 transition-all {selectedPresetAmount === String(amount) ? 'border-blue-600 bg-blue-50 shadow-xl shadow-blue-600/10' : 'border-slate-100 bg-white hover:border-blue-200'}">
                            <span class="text-[10px] font-black uppercase tracking-widest {selectedPresetAmount === String(amount) ? 'text-blue-600' : 'text-slate-400'}">Quick</span>
                            <span class="mt-1 text-2xl font-black text-slate-900">฿{amount}</span>
                        </div>
                    </label>
                {/each}
            </div>

            <label class="block group">
                <span class="text-slate-700">Or Custom Amount (Minimum ฿50)</span>
                <div class="relative mt-2">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-lg font-black text-slate-400">฿</span>
                    <input class="w-full pl-10 pr-6 py-5 text-xl font-black text-slate-900 rounded-[1.5rem]" name="custom_amount" type="number" bind:value={customAmount} oninput={customAmountChanged} placeholder="0.00" />
                </div>
            </label>

            <div class="space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-900">Payment Gateway</h3>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-[9px] font-black uppercase text-slate-500">Secure 256-bit</span>
                </div>

                <div class="grid gap-3">
                    {#each providers as provider (provider.value)}
                        <label class="group relative cursor-pointer">
                            <input class="sr-only" name="provider" type="radio" value={provider.value} bind:group={selectedProvider} />
                            <div class="flex items-center gap-5 rounded-[1.5rem] border-2 p-5 transition-all {selectedProvider === provider.value ? 'border-blue-600 bg-blue-50/50 shadow-lg' : 'border-slate-50 bg-white hover:border-slate-200'}">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white p-2 shadow-sm ring-1 ring-slate-100">
                                    <img alt="" class="h-full w-full object-contain" src={provider.logo} />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-black text-slate-900 uppercase tracking-tight">{provider.label}</p>
                                    <p class="text-xs font-bold text-slate-500">{provider.note}</p>
                                </div>
                                <div class="h-6 w-6 rounded-full border-2 border-slate-200 group-hover:border-blue-300 {selectedProvider === provider.value ? 'bg-blue-600 border-blue-600 ring-4 ring-blue-100' : ''}"></div>
                            </div>
                        </label>
                    {/each}
                </div>
            </div>

            {#if selectedProvider === 'credit_debit_card'}
                <div in:fade class="grid gap-5 rounded-[2.5rem] bg-slate-900 p-8 text-white shadow-2xl">
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-400 mb-2">Card Encryption Active</p>
                    <label class="block col-span-2">
                        <span class="text-slate-400">Cardholder Name</span>
                        <input class="w-full bg-white/10 border-none text-white placeholder:text-white/30" bind:value={cardholderName} placeholder="JOHN DOE" />
                    </label>
                    <label class="block col-span-2">
                        <span class="text-slate-400">Card Number</span>
                        <input class="w-full bg-white/10 border-none text-white tracking-[0.2em]" bind:value={cardNumber} oninput={cardNumberChanged} placeholder="0000 0000 0000 0000" />
                    </label>
                    <div class="grid grid-cols-2 gap-5">
                        <input class="bg-white/10 border-none text-white text-center" bind:value={expiryDate} oninput={expiryDateChanged} placeholder="MM/YY" />
                        <input class="bg-white/10 border-none text-white text-center" bind:value={cvv} oninput={cvvChanged} placeholder="CVV" />
                    </div>
                </div>
            {/if}

            <button class="btn-primary w-full py-6 text-base tracking-[0.3em]" type="submit">
                Authorize Payment
            </button>
        </form>

        <div class="space-y-6">
            <div class="panel h-full flex flex-col">
                <header class="mb-10">
                    <p class="eyebrow text-slate-500">ledger</p>
                    <h2 class="mt-2 text-3xl font-black text-slate-900 tracking-tight">Recent Activity</h2>
                </header>

                {#if data.transactions.data.length === 0}
                    <div class="flex flex-1 flex-col items-center justify-center rounded-[2.5rem] border-2 border-dashed border-slate-100 py-20 text-center">
                        <p class="text-sm font-black uppercase tracking-widest text-slate-400">No activity yet</p>
                    </div>
                {:else}
                    <div class="space-y-4">
                        {#each data.transactions.data as transaction (transaction.id)}
                            <div class="rounded-3xl border border-slate-100 bg-white p-6 transition-all hover:shadow-xl hover:shadow-blue-900/5">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-900 shadow-inner">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4" /></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-slate-900 uppercase tracking-tight">{transaction.type.replace('_', ' ')}</p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase">{providerLabel(transaction.provider)}</p>
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