<script lang="ts">
    import { fly, fade } from 'svelte/transition';
    import { enhance } from '$app/forms';
    let { form } = $props();

    // States
    const otpSent = $derived(Boolean(form?.otpSent));
    let showPassword = $state(false);
    let showConfirm = $state(false);
</script>

<div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-bl from-slate-50 via-sky-50/50 to-blue-50 px-4 py-12">
    <div class="absolute -right-32 top-20 h-[600px] w-[600px] animate-pulse rounded-full bg-sky-200/30 blur-3xl mix-blend-multiply opacity-50"></div>
    <div class="absolute -left-32 bottom-20 h-[600px] w-[600px] animate-pulse rounded-full bg-blue-200/30 blur-3xl mix-blend-multiply opacity-50" style="animation-delay: 1s;"></div>

    <div class="relative w-full max-w-[460px] rounded-[3rem] bg-white/80 px-10 py-12 shadow-2xl shadow-blue-900/5 ring-1 ring-slate-900/5 backdrop-blur-2xl" in:fly={{ y: 20, duration: 600 }}>
        
        <div class="text-center">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-xl shadow-slate-900/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900 uppercase">Create Account</h1>
            <p class="mt-2 text-[10px] font-black uppercase tracking-[0.3em] text-blue-600/70">Join our premium community</p>
        </div>

        {#if form?.error}
            <div in:fly={{ y: -10 }} class="mt-8 flex items-center gap-3 rounded-2xl bg-rose-50 px-5 py-4 text-[11px] font-black text-rose-700 ring-1 ring-rose-200 border border-rose-100 uppercase tracking-widest">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <p>{form.error}</p>
            </div>
        {/if}

        {#if !otpSent}
            <form class="mt-10 space-y-5" method="POST" action="?/requestOtp" use:enhance>
                <label class="group block">
                    <span class="mb-2 ml-4 block text-[10px] font-black uppercase tracking-widest text-slate-500 transition-colors group-focus-within:text-blue-600">Full Name</span>
                    <input
                        class="w-full rounded-[2rem] border-slate-200 bg-white/50 px-6 py-4 text-sm font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300"
                        name="name"
                        value={form?.name ?? ''}
                        placeholder="John Doe"
                        required
                    />
                </label>

                <label class="group block">
                    <span class="mb-2 ml-4 block text-[10px] font-black uppercase tracking-widest text-slate-500 transition-colors group-focus-within:text-blue-600">Email Address</span>
                    <input
                        class="w-full rounded-[2rem] border-slate-200 bg-white/50 px-6 py-4 text-sm font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300"
                        name="email"
                        type="email"
                        value={form?.email ?? ''}
                        placeholder="example@email.com"
                        required
                    />
                </label>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="group block">
                        <span class="mb-2 ml-4 block text-[10px] font-black uppercase tracking-widest text-slate-500 transition-colors group-focus-within:text-blue-600">Password</span>
                        <div class="relative">
                            <input
                                class="w-full rounded-[2rem] border-slate-200 bg-white/50 px-6 py-4 text-sm font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300"
                                name="password"
                                type={showPassword ? 'text' : 'password'}
                                placeholder="••••••••"
                                required
                            />
                            <button type="button" onclick={() => showPassword = !showPassword} class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    {#if showPassword}
                                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    {:else}
                                        <path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    {/if}
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="group block">
                        <span class="mb-2 ml-4 block text-[10px] font-black uppercase tracking-widest text-slate-500 transition-colors group-focus-within:text-blue-600">Confirm</span>
                        <div class="relative">
                            <input
                                class="w-full rounded-[2rem] border-slate-200 bg-white/50 px-6 py-4 text-sm font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300"
                                name="password_confirmation"
                                type={showConfirm ? 'text' : 'password'}
                                placeholder="••••••••"
                                required
                            />
                            <button type="button" onclick={() => showConfirm = !showConfirm} class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    {#if showConfirm}
                                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    {:else}
                                        <path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    {/if}
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button class="mt-6 w-full rounded-[1.5rem] bg-slate-900 py-5 text-[11px] font-black uppercase tracking-[0.4em] text-white shadow-xl shadow-slate-900/10 transition-all hover:-translate-y-1 hover:bg-blue-600 hover:shadow-blue-600/20 active:translate-y-0 active:scale-[0.98]" type="submit">
                    Send OTP
                </button>
            </form>
        {:else}
            <div in:fade class="mt-10 rounded-2xl bg-blue-50 px-6 py-5 text-center ring-1 ring-blue-200">
                <p class="text-[11px] font-black text-blue-700 uppercase tracking-widest leading-relaxed">
                    Check your email <br/> <span class="text-slate-900 lowercase">{form?.email}</span>
                </p>
            </div>

            <form class="mt-8 space-y-5" method="POST" action="?/verifyOtp" use:enhance>
                <input type="hidden" name="name" value={form?.name ?? ''} />
                <input type="hidden" name="email" value={form?.email ?? ''} />

                <label class="group block">
                    <span class="mb-2 ml-4 block text-[10px] font-black uppercase tracking-widest text-slate-500 transition-colors group-focus-within:text-blue-600 text-center">Enter 6-Digit Code</span>
                    <input
                        class="w-full rounded-[2rem] border-slate-200 bg-white/50 px-5 py-5 text-center text-2xl font-black tracking-[0.5em] text-slate-900 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300 placeholder:text-slate-200"
                        name="otp"
                        inputmode="numeric"
                        maxlength="6"
                        placeholder="000000"
                        required
                    />
                </label>

                <button class="mt-6 w-full rounded-[1.5rem] bg-slate-900 py-5 text-[11px] font-black uppercase tracking-[0.4em] text-white shadow-xl shadow-slate-900/10 transition-all hover:-translate-y-1 hover:bg-blue-600 active:translate-y-0" type="submit">
                    Verify & Create Account
                </button>
            </form>

            <a class="mt-4 flex w-full items-center justify-center rounded-2xl border border-slate-100 py-3.5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 transition-all hover:bg-slate-50 hover:text-blue-600" href="/register">
                Start Over
            </a>
        {/if}

        <div class="mt-10 pt-8 border-t border-slate-100 text-center">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                Already a member? <a class="ml-1 text-slate-900 hover:text-blue-600 transition-all underline underline-offset-8" href="/login">Sign In</a>
            </p>
        </div>
    </div>
</div>