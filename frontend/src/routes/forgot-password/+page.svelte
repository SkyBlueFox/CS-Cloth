<script lang="ts">
	import { fly } from 'svelte/transition';
	let { form } = $props();
	const otpSent = $derived(Boolean(form?.otpSent));
</script>

<div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-slate-50 via-sky-50/50 to-blue-50 px-4">
	<div class="absolute -left-32 top-10 h-[600px] w-[600px] animate-pulse rounded-full bg-sky-200/30 blur-3xl mix-blend-multiply" style="animation-duration: 8s;"></div>
	<div class="absolute -right-32 bottom-10 h-[600px] w-[600px] animate-pulse rounded-full bg-blue-200/30 blur-3xl mix-blend-multiply" style="animation-duration: 12s; animation-delay: 2s;"></div>

	<div class="relative w-full max-w-[460px] rounded-[3rem] bg-white/80 px-10 py-12 shadow-2xl shadow-blue-900/5 ring-1 ring-slate-900/5 backdrop-blur-2xl" in:fly={{ y: 20, duration: 600 }}>
		<div class="text-center">
			<div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-xl shadow-slate-900/20">
				<span class="text-2xl font-black tracking-wider">CS</span>
			</div>
			<h1 class="text-3xl font-black tracking-tight text-slate-900">Reset Password</h1>
			<p class="mt-2 text-sm font-bold uppercase tracking-[0.2em] text-blue-600/70">Request an OTP</p>
		</div>

		{#if form?.error}
			<div in:fly={{ y: -10 }} class="mt-8 rounded-2xl bg-rose-50 px-5 py-4 text-sm font-bold text-rose-700 ring-1 ring-rose-200">
				{form.error}
			</div>
		{/if}

		{#if form?.success}
			<div in:fly={{ y: -10 }} class="mt-8 rounded-2xl bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700 ring-1 ring-emerald-200">
				{form.success}
			</div>
		{/if}

		{#if !otpSent}
			<form class="mt-10 space-y-6" method="POST" action="?/requestOtp">
				<label class="group block">
					<span class="mb-2 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 transition-colors group-focus-within:text-blue-600">Email Address</span>
					<input
						class="w-full rounded-2xl border-slate-200 bg-white/50 px-5 py-4 text-sm font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300"
						name="email"
						type="email"
						value={form?.email ?? ''}
						placeholder="name@company.com"
						required
					/>
				</label>

				<button class="mt-4 w-full rounded-2xl bg-slate-900 py-4.5 text-sm font-black uppercase tracking-[0.2em] text-white shadow-xl shadow-slate-900/20 transition-all hover:-translate-y-1 hover:bg-blue-600 hover:shadow-blue-600/30 active:translate-y-0 active:scale-95" type="submit">
					Send OTP
				</button>
			</form>
		{:else}
			<div class="mt-10 rounded-2xl bg-blue-50 px-5 py-4 text-sm font-bold text-blue-700 ring-1 ring-blue-200">
				Enter the 6-digit OTP sent to <span class="text-slate-900">{form?.email}</span>, then choose your new password.
			</div>

			<form class="mt-6 space-y-6" method="POST" action="?/resetPassword">
				<input type="hidden" name="email" value={form?.email ?? ''} />

				<label class="group block">
					<span class="mb-2 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 transition-colors group-focus-within:text-blue-600">OTP Code</span>
					<input
						class="w-full rounded-2xl border-slate-200 bg-white/50 px-5 py-4 text-center text-lg font-black tracking-[0.35em] text-slate-900 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300"
						name="otp"
						inputmode="numeric"
						maxlength="6"
						minlength="6"
						placeholder="000000"
						required
					/>
				</label>

				<label class="group block">
					<span class="mb-2 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 transition-colors group-focus-within:text-blue-600">New Password</span>
					<input
						class="w-full rounded-2xl border-slate-200 bg-white/50 px-5 py-4 text-sm font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300"
						name="password"
						type="password"
						required
					/>
				</label>

				<label class="group block">
					<span class="mb-2 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 transition-colors group-focus-within:text-blue-600">Confirm Password</span>
					<input
						class="w-full rounded-2xl border-slate-200 bg-white/50 px-5 py-4 text-sm font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300"
						name="password_confirmation"
						type="password"
						required
					/>
				</label>

				<button class="mt-4 w-full rounded-2xl bg-slate-900 py-4.5 text-sm font-black uppercase tracking-[0.2em] text-white shadow-xl shadow-slate-900/20 transition-all hover:-translate-y-1 hover:bg-blue-600 hover:shadow-blue-600/30 active:translate-y-0 active:scale-95" type="submit">
					Reset Password
				</button>
			</form>
		{/if}

		<div class="mt-10 border-t border-slate-100 pt-8 text-center">
			<p class="text-sm font-bold text-slate-500">
				Remembered it?
				<a class="ml-1 text-blue-600 transition-all underline underline-offset-4 hover:text-blue-800" href="/login">Back to login</a>
			</p>
		</div>
	</div>
</div>
