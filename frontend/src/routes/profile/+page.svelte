<script lang="ts">
	let { data, form } = $props();
</script>

<section class="grid gap-8 xl:grid-cols-[0.95fr_1.05fr]">
	<form class="panel space-y-4" method="POST" action="?/updateProfile">
		<div>
			<p class="eyebrow">Profile</p>
			<h1 class="mt-2 text-3xl font-semibold">Update your account</h1>
		</div>
		{#if form?.error}
			<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
		{/if}
		{#if form?.success}
			<p class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{form.success}</p>
		{/if}
		<label class="block">
			<span class="mb-1 block text-sm font-medium">Name</span>
			<input class="w-full rounded-2xl border-slate-300" name="name" value={data.user?.name ?? ''} required />
		</label>
		<label class="block">
			<span class="mb-1 block text-sm font-medium">Email</span>
			<input class="w-full rounded-2xl border-slate-300" name="email" type="email" value={data.user?.email ?? ''} required />
		</label>
		<label class="block">
			<span class="mb-1 block text-sm font-medium">Phone</span>
			<input class="w-full rounded-2xl border-slate-300" name="phone" value={data.user?.phone ?? ''} />
		</label>
		<label class="block">
			<span class="mb-1 block text-sm font-medium">New password</span>
			<input class="w-full rounded-2xl border-slate-300" name="password" type="password" />
		</label>
		<label class="block">
			<span class="mb-1 block text-sm font-medium">Confirm new password</span>
			<input class="w-full rounded-2xl border-slate-300" name="password_confirmation" type="password" />
		</label>
		<button class="btn-primary" type="submit">Save changes</button>
	</form>

	{#if data.user?.role === 'user'}
		<div class="space-y-6">
			<div class="panel space-y-4">
				<div>
					<p class="eyebrow">Addresses</p>
					<h2 class="mt-2 text-3xl font-semibold">Saved shipping addresses</h2>
				</div>
				{#if form?.addressError}
					<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.addressError}</p>
				{/if}
				{#if form?.addressSuccess}
					<p class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{form.addressSuccess}</p>
				{/if}
				<div class="space-y-4">
					{#each data.addresses as address}
						<form class="subtle-box grid gap-3 p-4" method="POST" action="?/saveAddress">
							<input name="address_id" type="hidden" value={address.id} />
							<div class="grid gap-3 md:grid-cols-2">
								<input class="rounded-2xl border-slate-300" name="label" placeholder="Label" value={address.label} required />
								<input class="rounded-2xl border-slate-300" name="recipient_name" placeholder="Recipient name" value={address.recipient_name} required />
								<input class="rounded-2xl border-slate-300" name="phone" placeholder="Phone" value={address.phone} required />
								<input class="rounded-2xl border-slate-300" name="country" placeholder="Country" value={address.country} required />
								<input class="rounded-2xl border-slate-300 md:col-span-2" name="line_1" placeholder="Address line 1" value={address.line_1} required />
								<input class="rounded-2xl border-slate-300 md:col-span-2" name="line_2" placeholder="Address line 2" value={address.line_2 ?? ''} />
								<input class="rounded-2xl border-slate-300" name="district" placeholder="District" value={address.district} required />
								<input class="rounded-2xl border-slate-300" name="province" placeholder="Province" value={address.province} required />
								<input class="rounded-2xl border-slate-300" name="postal_code" placeholder="Postal code" value={address.postal_code} required />
							</div>
							<label class="flex items-center gap-2 text-sm font-medium text-slate-700">
								<input checked={address.is_default} name="is_default" type="checkbox" value="1" />
								Set as default
							</label>
							<div class="flex gap-3">
								<button class="btn-secondary" type="submit">Save address</button>
								<button class="btn-danger" formaction="?/deleteAddress" type="submit">Delete</button>
							</div>
						</form>
					{/each}
				</div>
			</div>

			<form class="panel grid gap-3" method="POST" action="?/saveAddress">
				<div>
					<p class="eyebrow">New Address</p>
					<h2 class="mt-2 text-2xl font-semibold">Add another address</h2>
				</div>
				<div class="grid gap-3 md:grid-cols-2">
					<input class="rounded-2xl border-slate-300" name="label" placeholder="Home, Dorm, Office" required />
					<input class="rounded-2xl border-slate-300" name="recipient_name" placeholder="Recipient name" required />
					<input class="rounded-2xl border-slate-300" name="phone" placeholder="Phone" required />
					<input class="rounded-2xl border-slate-300" name="country" placeholder="Country" value="Thailand" required />
					<input class="rounded-2xl border-slate-300 md:col-span-2" name="line_1" placeholder="Address line 1" required />
					<input class="rounded-2xl border-slate-300 md:col-span-2" name="line_2" placeholder="Address line 2" />
					<input class="rounded-2xl border-slate-300" name="district" placeholder="District" required />
					<input class="rounded-2xl border-slate-300" name="province" placeholder="Province" required />
					<input class="rounded-2xl border-slate-300" name="postal_code" placeholder="Postal code" required />
				</div>
				<label class="flex items-center gap-2 text-sm font-medium text-slate-700">
					<input name="is_default" type="checkbox" value="1" />
					Set as default
				</label>
				<button class="btn-primary" type="submit">Add address</button>
			</form>
		</div>
	{/if}
</section>
