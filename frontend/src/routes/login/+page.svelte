<script lang="ts">
    let { form } = $props();
</script>

<div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-slate-50 via-sky-50/50 to-blue-50 px-4">
    <div class="absolute -left-32 top-10 h-[500px] w-[500px] animate-pulse rounded-full bg-sky-200/40 blur-3xl mix-blend-multiply" style="animation-duration: 7s;"></div>
    <div class="absolute -right-32 bottom-10 h-[500px] w-[500px] animate-pulse rounded-full bg-blue-200/40 blur-3xl mix-blend-multiply" style="animation-duration: 10s; animation-delay: 2s;"></div>

    <div class="relative w-full max-w-[420px] space-y-8 rounded-[2.5rem] bg-white/70 px-10 py-12 shadow-2xl shadow-sky-900/5 ring-1 ring-slate-900/5 backdrop-blur-xl">
        
        <div class="text-center">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-tr from-sky-600 to-blue-600 shadow-lg shadow-sky-500/30">
                <span class="text-2xl font-bold tracking-wider text-white">CS</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">เข้าสู่ระบบ</h1>
            <p class="mt-2 text-sm text-slate-500">ยินดีต้อนรับกลับสู่ CS Cloth</p>
        </div>

        {#if form?.error}
            <div class="flex items-center gap-3 rounded-2xl bg-rose-50 px-5 py-4 text-sm text-rose-700 ring-1 ring-rose-600/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <p>{form.error}</p>
            </div>
        {/if}

        <form class="space-y-5" method="POST">
            <label class="block group">
                <span class="mb-1.5 block text-sm font-medium text-slate-700 transition-colors group-focus-within:text-blue-600">อีเมล</span>
                <input
                    class="w-full rounded-2xl border-slate-200 bg-white/50 px-4 py-3.5 text-sm transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 hover:bg-white"
                    name="email"
                    type="email"
                    value={form?.email ?? ''}
                    placeholder="example@email.com"
                    required
                />
            </label>

            <label class="block group">
                <div class="mb-1.5 flex items-center justify-between">
                    <span class="block text-sm font-medium text-slate-700 transition-colors group-focus-within:text-blue-600">รหัสผ่าน</span>
                </div>
                <input
                    class="w-full rounded-2xl border-slate-200 bg-white/50 px-4 py-3.5 text-sm transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 hover:bg-white"
                    name="password"
                    type="password"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                />
            </label>

            <button class="mt-8 w-full rounded-2xl bg-slate-900 px-4 py-3.5 text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-600 hover:shadow-lg hover:shadow-blue-600/25 active:translate-y-0" type="submit">
                เข้าสู่ระบบ
            </button>
        </form>

        <p class="text-center text-sm font-medium text-slate-500">
            ยังไม่มีบัญชี? <a class="text-blue-600 hover:text-blue-800 hover:underline underline-offset-4 transition-all" href="/register">สมัครสมาชิกเลย</a>
        </p>
    </div>
</div>