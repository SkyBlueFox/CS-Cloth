<script lang="ts">
    import { fly, fade } from 'svelte/transition';
    let { data, form } = $props();
    const profileLocked = $derived(data.user?.role === 'admin');
</script>

<section class="grid gap-12 xl:grid-cols-[0.8fr_1.2fr]">
    <div class="space-y-6">
        <form class="relative overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm transition-all sm:p-10" method="POST" action="?/updateProfile">
            <header class="mb-10">
                <div class="flex items-center gap-2">
                    <span class="h-1.5 w-8 rounded-full bg-blue-600"></span>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600">Settings</p>
                </div>
                <h1 class="mt-4 text-4xl font-black tracking-tight text-slate-900">Personal Info</h1>
            </header>

            {#if profileLocked}
                <div class="mb-8 flex items-start gap-3 rounded-2xl bg-slate-100 p-5 ring-1 ring-slate-200" in:fade>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-slate-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-xs font-bold leading-relaxed text-slate-600 uppercase tracking-wide">
                        Admin profiles are restricted. Contact Superadmin to change data.
                    </p>
                </div>
            {/if}

            {#if form?.error}
                <div class="mb-6 rounded-2xl bg-rose-50 px-5 py-4 text-sm font-bold text-rose-700 ring-1 ring-rose-200" in:fly={{ y: -10 }}>{form.error}</div>
            {/if}
            {#if form?.success}
                <div class="mb-6 rounded-2xl bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700 ring-1 ring-emerald-200" in:fly={{ y: -10 }}>{form.success}</div>
            {/if}

            <div class="space-y-6">
                <label class="group block">
                    <span class="mb-2 ml-1 block text-[10px] font-black uppercase tracking-wider text-slate-500 transition-colors group-focus-within:text-blue-600">Full Name</span>
                    <input
                        class="w-full rounded-2xl border-slate-200 bg-slate-50 px-5 py-4 text-sm font-black text-slate-900 transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 disabled:opacity-50"
                        disabled={profileLocked}
                        name="name"
                        value={data.user?.name ?? ''}
                        placeholder="John Doe"
                        required
                    />
                </label>

                <label class="group block">
                    <span class="mb-2 ml-1 block text-[10px] font-black uppercase tracking-wider text-slate-500 transition-colors group-focus-within:text-blue-600">Email Address</span>
                    <input
                        class="w-full rounded-2xl border-slate-200 bg-slate-50 px-5 py-4 text-sm font-black text-slate-900 transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 disabled:opacity-50"
                        disabled={profileLocked}
                        name="email"
                        type="email"
                        value={data.user?.email ?? ''}
                        placeholder="example@mail.com"
                        required
                    />
                </label>

                <label class="group block">
                    <span class="mb-2 ml-1 block text-[10px] font-black uppercase tracking-wider text-slate-500 transition-colors group-focus-within:text-blue-600">Phone Number</span>
                    <input
                        class="w-full rounded-2xl border-slate-200 bg-slate-50 px-5 py-4 text-sm font-black text-slate-900 transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 disabled:opacity-50"
                        disabled={profileLocked}
                        name="phone"
                        value={data.user?.phone ?? ''}
                        placeholder="081-XXX-XXXX"
                    />
                </label>

                <div class="h-px w-full bg-slate-100 my-10"></div>

                <div class="grid gap-6">
                    <label class="group block">
                        <span class="mb-2 ml-1 block text-[10px] font-black uppercase tracking-wider text-slate-500 transition-colors group-focus-within:text-blue-600">New Password</span>
                        <input
                            class="w-full rounded-2xl border-slate-200 bg-slate-50 px-5 py-4 text-sm font-black text-slate-900 transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 disabled:opacity-50"
                            disabled={profileLocked}
                            name="password"
                            type="password"
                            placeholder="••••••••"
                        />
                    </label>

                    <label class="group block">
                        <span class="mb-2 ml-1 block text-[10px] font-black uppercase tracking-wider text-slate-500 transition-colors group-focus-within:text-blue-600">Confirm Password</span>
                        <input
                            class="w-full rounded-2xl border-slate-200 bg-slate-50 px-5 py-4 text-sm font-black text-slate-900 transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 disabled:opacity-50"
                            disabled={profileLocked}
                            name="password_confirmation"
                            type="password"
                            placeholder="••••••••"
                        />
                    </label>
                </div>

                <button 
                    class="mt-6 w-full rounded-2xl bg-slate-900 py-5 text-sm font-black uppercase tracking-[0.2em] text-white shadow-xl shadow-slate-900/20 transition-all hover:-translate-y-1 hover:bg-blue-600 hover:shadow-blue-600/30 active:translate-y-0 active:scale-95 disabled:bg-slate-200 disabled:text-slate-400 disabled:shadow-none" 
                    disabled={profileLocked} 
                    type="submit"
                >
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {#if data.user?.role === 'user'}
        <div class="flex flex-col gap-8">
            <section class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
                <header class="mb-10 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Logistics</p>
                        <h2 class="mt-2 text-3xl font-black text-slate-900">Shipping Addresses</h2>
                    </div>
                </header>

                {#if form?.addressError}
                    <div class="mb-6 rounded-2xl bg-rose-50 px-5 py-4 text-sm font-bold text-rose-700 ring-1 ring-rose-200">{form.addressError}</div>
                {/if}
                {#if form?.addressSuccess}
                    <div class="mb-6 rounded-2xl bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700 ring-1 ring-emerald-200">{form.addressSuccess}</div>
                {/if}

                <div class="grid gap-8">
                    {#each data.addresses as address (address.id)}
                        <form 
                            class="relative rounded-[2.5rem] border border-slate-100 bg-slate-50/50 p-8 transition-all hover:bg-white hover:shadow-2xl hover:shadow-blue-900/5" 
                            method="POST" 
                            action="?/saveAddress"
                        >
                            <input name="address_id" type="hidden" value={address.id} />
                            
                            <div class="mb-8 flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 font-black text-sm uppercase">
                                        {address.label.charAt(0)}
                                    </span>
                                    <input class="bg-transparent border-none p-0 text-xl font-black text-slate-900 focus:ring-0 w-48" name="label" value={address.label} />
                                </div>
                                {#if address.is_default}
                                    <span class="rounded-full bg-blue-600 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-blue-600/20">Primary</span>
                                {/if}
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <label class="block">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1 block">Recipient</span>
                                    <input class="w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900" name="recipient_name" value={address.recipient_name} required />
                                </label>
                                <label class="block">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1 block">Phone</span>
                                    <input class="w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900" name="phone" value={address.phone} required />
                                </label>
                                <label class="block md:col-span-2">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1 block">Full Address</span>
                                    <input class="w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900" name="line_1" value={address.line_1} required />
                                </label>
                                <input class="w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900" name="district" placeholder="District" value={address.district} required />
                                <input class="w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900" name="province" placeholder="Province" value={address.province} required />
                            </div>

                            <div class="mt-8 flex items-center justify-between border-t border-slate-200/60 pt-6">
                                <label class="flex cursor-pointer items-center gap-3 text-[11px] font-black uppercase tracking-widest text-slate-500 hover:text-blue-600 transition-colors">
                                    <input checked={address.is_default} name="is_default" type="checkbox" value="1" class="h-5 w-5 rounded-lg border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    Set as default
                                </label>
                                <div class="flex gap-3">
                                    <button class="rounded-xl px-5 py-2.5 text-[11px] font-black uppercase tracking-widest text-slate-600 transition-colors hover:bg-slate-200 hover:text-slate-900" type="submit">Update</button>
                                    <button class="rounded-xl px-5 py-2.5 text-[11px] font-black uppercase tracking-widest text-rose-500 transition-colors hover:bg-rose-100 hover:text-rose-700" formaction="?/deleteAddress" type="submit">Remove</button>
                                </div>
                            </div>
                        </form>
                    {/each}

                    <div class="rounded-[2.5rem] border-2 border-dashed border-slate-200 bg-slate-50/30 p-10 text-center transition-all hover:border-blue-300">
                        <details class="group/new">
                            <summary class="flex list-none flex-col items-center justify-center cursor-pointer">
                                <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-200 transition-all group-hover/new:bg-blue-600 group-hover/new:text-white group-hover/new:scale-110">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4" /></svg>
                                </div>
                                <span class="text-sm font-black uppercase tracking-[0.2em] text-slate-500 group-hover/new:text-blue-600">Add New Destination</span>
                            </summary>
                            <form class="mt-10 grid gap-5 text-left" method="POST" action="?/saveAddress">
                                <div class="grid gap-5 md:grid-cols-2">
                                    <input class="rounded-2xl border-slate-200 px-5 py-3.5 text-sm font-bold" name="label" placeholder="Label (e.g. Office)" required />
                                    <input class="rounded-2xl border-slate-200 px-5 py-3.5 text-sm font-bold" name="recipient_name" placeholder="Recipient name" required />
                                    <input class="rounded-2xl border-slate-200 px-5 py-3.5 text-sm font-bold" name="phone" placeholder="Phone" required />
                                    <input class="rounded-2xl border-slate-200 px-5 py-3.5 text-sm font-bold" name="country" value="Thailand" readonly />
                                    <input class="rounded-2xl border-slate-200 px-5 py-3.5 text-sm font-bold md:col-span-2" name="line_1" placeholder="Street address, house no." required />
                                    <input class="rounded-2xl border-slate-200 px-5 py-3.5 text-sm font-bold" name="district" placeholder="District" required />
                                    <input class="rounded-2xl border-slate-200 px-5 py-3.5 text-sm font-bold" name="province" placeholder="Province" required />
                                    <input class="rounded-2xl border-slate-200 px-5 py-3.5 text-sm font-bold md:col-span-2" name="postal_code" placeholder="Postal code" required />
                                </div>
                                <button class="mt-6 w-full rounded-2xl bg-blue-600 py-5 text-xs font-black uppercase tracking-[0.3em] text-white shadow-xl shadow-blue-600/30 transition-all hover:bg-blue-700" type="submit">
                                    Save New Address
                                </button>
                            </form>
                        </details>
                    </div>
                </div>
            </section>
        </div>
    {/if}
</section>