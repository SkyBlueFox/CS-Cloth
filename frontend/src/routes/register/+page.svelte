<script lang="ts">
    import { fly } from 'svelte/transition';
    let { form } = $props();
</script>

<div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-bl from-slate-50 via-sky-50/50 to-blue-50 px-4 py-12">
    <div class="absolute -right-32 top-20 h-[600px] w-[600px] animate-pulse rounded-full bg-sky-200/30 blur-3xl mix-blend-multiply" style="animation-duration: 10s;"></div>
    <div class="absolute -left-32 bottom-20 h-[600px] w-[600px] animate-pulse rounded-full bg-blue-200/30 blur-3xl mix-blend-multiply" style="animation-duration: 15s; animation-delay: 1s;"></div>

    <div class="relative w-full max-w-[460px] rounded-[3rem] bg-white/80 px-10 py-12 shadow-2xl shadow-blue-900/5 ring-1 ring-slate-900/5 backdrop-blur-2xl" in:fly={{ y: 20, duration: 600 }}>
        
        <div class="text-center">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-xl shadow-blue-600/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900">Create Account</h1>
            <p class="mt-2 text-sm font-bold uppercase tracking-[0.2em] text-slate-400">Join our community</p>
        </div>

        {#if form?.error}
            <div in:fly={{ y: -10 }} class="mt-8 flex items-center gap-3 rounded-2xl bg-rose-50 px-5 py-4 text-sm font-bold text-rose-700 ring-1 ring-rose-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <p>{form.error}</p>
            </div>
        {/if}

        <form class="mt-10 space-y-5" method="POST">
            <label class="group block">
                <span class="mb-2 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 transition-colors group-focus-within:text-blue-600">Full Name</span>
                <input
                    class="w-full rounded-2xl border-slate-200 bg-white/50 px-5 py-4 text-sm font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300"
                    name="name"
                    value={form?.name ?? ''}
                    placeholder="John Doe"
                    required
                />
            </label>

            <label class="group block">
                <span class="mb-2 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 transition-colors group-focus-within:text-blue-600">Email Address</span>
                <input
                    class="w-full rounded-2xl border-slate-200 bg-white/50 px-5 py-4 text-sm font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300"
                    name="email"
                    type="email"
                    value={form?.email ?? ''}
                    placeholder="example@email.com"
                    required
                />
            </label>

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="group block">
                    <span class="mb-2 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 transition-colors group-focus-within:text-blue-600">Password</span>
                    <input
                        class="w-full rounded-2xl border-slate-200 bg-white/50 px-5 py-4 text-sm font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300"
                        name="password"
                        type="password"
                        placeholder="••••••••"
                        required
                    />
                </label>

                <label class="group block">
                    <span class="mb-2 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 transition-colors group-focus-within:text-blue-600">Confirm</span>
                    <input
                        class="w-full rounded-2xl border-slate-200 bg-white/50 px-5 py-4 text-sm font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300"
                        name="password_confirmation"
                        type="password"
                        placeholder="••••••••"
                        required
                    />
                </label>
            </div>

            <button class="mt-6 w-full rounded-2xl bg-slate-900 py-4.5 text-sm font-black uppercase tracking-[0.2em] text-white shadow-xl shadow-slate-900/20 transition-all hover:-translate-y-1 hover:bg-blue-600 hover:shadow-blue-600/30 active:translate-y-0 active:scale-95" type="submit">
                Create Account
            </button>
        </form>

        <div class="mt-10 pt-8 border-t border-slate-100 text-center">
            <p class="text-sm font-bold text-slate-500">
                Already a member? <a class="ml-1 text-slate-900 hover:text-blue-600 transition-all underline underline-offset-4" href="/login">Sign In</a>
            </p>
        </div>
    </div>
</div>