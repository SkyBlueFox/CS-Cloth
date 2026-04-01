<script lang="ts">
	import Pagination from '$lib/components/Pagination.svelte';
	import { itemImageSrc } from '$lib/media';

	let { data, form } = $props();
	let searchQuery = $state('');
	let sortOrder = $state<'newest' | 'oldest'>('newest');

	const filteredReports = $derived(
		[...data.reports.data]
			.filter((report) => {
				const query = searchQuery.trim().toLowerCase();
				if (!query) {
					return true;
				}

				return [
					report.reporter_name,
					report.admin_name,
					report.reason,
					report.question_text_snapshot,
					report.answer_text_snapshot
				]
					.filter(Boolean)
					.some((value) => value.toLowerCase().includes(query));
			})
			.sort((a, b) => {
				const aTime = a.created_at ? new Date(a.created_at).getTime() : 0;
				const bTime = b.created_at ? new Date(b.created_at).getTime() : 0;

				return sortOrder === 'newest' ? bTime - aTime : aTime - bTime;
			})
	);
	const pendingReports = $derived(filteredReports.filter((report) => report.status === 'pending'));
	const resolvedReports = $derived(filteredReports.filter((report) => report.status === 'resolved'));
	const dismissedReports = $derived(filteredReports.filter((report) => report.status === 'dismissed'));

	function formatReportDate(value: string | null) {
		if (!value) {
			return 'Unknown time';
		}

		return new Intl.DateTimeFormat('en-US', {
			dateStyle: 'medium',
			timeStyle: 'short'
		}).format(new Date(value));
	}
</script>

<section class="space-y-6">
	<div class="panel">
		<p class="eyebrow">Superadmin</p>
		<h1 class="mt-2 text-3xl font-semibold">Moderation reports</h1>
		<p class="mt-3 max-w-2xl text-sm text-slate-600">Review pending flags first, then use the archive sections below to inspect already-closed reports without mixing them into the active queue.</p>
	</div>
	{#if form?.error}
		<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
	{/if}
	{#if form?.success}
		<p class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{form.success}</p>
	{/if}

	<div class="panel grid gap-4 md:grid-cols-[1fr_16rem]">
		<label class="block">
			<span class="mb-2 block text-sm font-medium text-slate-700">Search reports</span>
			<input
				bind:value={searchQuery}
				class="w-full rounded-2xl border-slate-300"
				placeholder="Search by reporter, admin, reason, question, or answer"
				type="search"
			/>
		</label>
		<label class="block">
			<span class="mb-2 block text-sm font-medium text-slate-700">Sort order</span>
			<select bind:value={sortOrder} class="w-full rounded-2xl border-slate-300">
				<option value="newest">Newest first</option>
				<option value="oldest">Oldest first</option>
			</select>
		</label>
	</div>

	<div class="grid gap-4 md:grid-cols-3">
		<div class="panel border-amber-200 bg-amber-50/70">
			<p class="eyebrow text-amber-700">Pending</p>
			<p class="mt-3 text-4xl font-semibold text-amber-900">{pendingReports.length}</p>
			<p class="mt-2 text-sm text-amber-800">Reports waiting for a moderation decision.</p>
		</div>
		<div class="panel border-emerald-200 bg-emerald-50/70">
			<p class="eyebrow text-emerald-700">Resolved</p>
			<p class="mt-3 text-4xl font-semibold text-emerald-900">{resolvedReports.length}</p>
			<p class="mt-2 text-sm text-emerald-800">Reports where the answer was removed.</p>
		</div>
		<div class="panel border-slate-200 bg-slate-50/80">
			<p class="eyebrow text-slate-600">Dismissed</p>
			<p class="mt-3 text-4xl font-semibold text-slate-900">{dismissedReports.length}</p>
			<p class="mt-2 text-sm text-slate-700">Reports closed without moderator action.</p>
		</div>
	</div>

	<section class="space-y-4">
		<div class="flex items-end justify-between gap-4">
			<div>
				<p class="eyebrow">Active Queue</p>
				<h2 class="mt-2 text-2xl font-semibold">Pending reports</h2>
			</div>
			<p class="text-sm text-slate-500">{pendingReports.length} items</p>
		</div>

		{#if pendingReports.length === 0}
			<div class="panel border-emerald-200 bg-emerald-50/60">
				<p class="font-medium text-emerald-900">No pending reports.</p>
				<p class="mt-1 text-sm text-emerald-800">The moderation queue is currently clear.</p>
			</div>
		{:else}
			<div class="space-y-5">
				{#each pendingReports as report}
					<article class="panel space-y-4 border-amber-200/80">
						<div class="flex flex-wrap items-center justify-between gap-4">
							<div>
								<h2 class="text-xl font-semibold">Report #{report.id}</h2>
								<div class="mt-1 flex items-center gap-3 text-sm text-slate-500">
									<span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">pending</span>
									<span>{report.reporter_name}</span>
									<span>→</span>
									<span>{report.admin_name}</span>
								</div>
								<p class="mt-2 text-xs text-slate-500">Submitted {formatReportDate(report.created_at)}</p>
							</div>
							<div class="flex gap-3">
								<form method="POST" action="?/resolve">
									<input name="report_id" type="hidden" value={report.id} />
									<button class="rounded-full border border-emerald-300 px-4 py-2 text-sm text-emerald-700" type="submit">Resolve</button>
								</form>
								<form method="POST" action="?/dismiss">
									<input name="report_id" type="hidden" value={report.id} />
									<button class="btn-secondary rounded-full px-4 py-2 text-sm" type="submit">Dismiss</button>
								</form>
							</div>
						</div>
						{#if report.item}
							<div class="flex items-center gap-4 rounded-[1.25rem] bg-slate-50 p-4">
								{#if itemImageSrc(report.item)}
									<img
										alt={report.item.name}
										class="h-20 w-20 rounded-[1rem] object-cover"
										src={itemImageSrc(report.item) ?? undefined}
									/>
								{/if}
								<div>
									<p class="text-sm font-medium text-slate-600">Item</p>
									<p class="mt-1 font-semibold text-slate-900">{report.item.name}</p>
								</div>
							</div>
						{/if}
						<div class="rounded-[1.25rem] bg-sky-50 p-4">
							<p class="text-sm font-medium text-slate-600">Question</p>
							<p class="mt-2">{report.question_text_snapshot}</p>
						</div>
						<div class="rounded-[1.25rem] bg-rose-50 p-4">
							<p class="text-sm font-medium text-rose-700">Reported answer</p>
							<p class="mt-2">{report.answer_text_snapshot}</p>
						</div>
						<div class="rounded-[1.25rem] bg-amber-50 p-4">
							<p class="text-sm font-medium text-amber-700">Reason</p>
							<p class="mt-2 text-slate-700">{report.reason}</p>
						</div>
					</article>
				{/each}
			</div>
		{/if}
	</section>

	<div class="grid gap-6 xl:grid-cols-2">
		<section class="space-y-4">
			<div class="flex items-end justify-between gap-4">
				<div>
					<p class="eyebrow">Archive</p>
					<h2 class="mt-2 text-xl font-semibold">Resolved</h2>
				</div>
				<p class="text-sm text-slate-500">{resolvedReports.length} items</p>
			</div>
			<div class="space-y-4">
				{#if resolvedReports.length === 0}
					<div class="panel border-emerald-200/80 bg-emerald-50/50">
						<p class="text-sm text-emerald-800">No resolved reports on this page.</p>
					</div>
				{:else}
					{#each resolvedReports as report}
						<article class="panel space-y-3 border-emerald-200/80 bg-emerald-50/35">
							<div class="flex items-center justify-between gap-3">
								<h3 class="font-semibold">Report #{report.id}</h3>
								<span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">resolved</span>
							</div>
							<p class="text-sm text-slate-600">{report.reporter_name} → {report.admin_name}</p>
							<p class="text-xs text-slate-500">Submitted {formatReportDate(report.created_at)}</p>
							{#if report.item}
								<div class="flex items-center gap-3 rounded-[1rem] bg-white/70 p-3">
									{#if itemImageSrc(report.item)}
										<img
											alt={report.item.name}
											class="h-14 w-14 rounded-[0.9rem] object-cover"
											src={itemImageSrc(report.item) ?? undefined}
										/>
									{/if}
									<div>
										<p class="text-xs font-medium uppercase tracking-[0.14em] text-slate-500">Item</p>
										<p class="text-sm font-medium text-slate-900">{report.item.name}</p>
									</div>
								</div>
							{/if}
							<p class="text-sm text-slate-700"><span class="font-medium">Question:</span> {report.question_text_snapshot}</p>
							<p class="text-sm text-slate-700"><span class="font-medium">Reason:</span> {report.reason}</p>
						</article>
					{/each}
				{/if}
			</div>
		</section>

		<section class="space-y-4">
			<div class="flex items-end justify-between gap-4">
				<div>
					<p class="eyebrow">Archive</p>
					<h2 class="mt-2 text-xl font-semibold">Dismissed</h2>
				</div>
				<p class="text-sm text-slate-500">{dismissedReports.length} items</p>
			</div>
			<div class="space-y-4">
				{#if dismissedReports.length === 0}
					<div class="panel border-slate-200 bg-slate-50/70">
						<p class="text-sm text-slate-600">No dismissed reports on this page.</p>
					</div>
				{:else}
					{#each dismissedReports as report}
						<article class="panel space-y-3 border-slate-200 bg-slate-50/70">
							<div class="flex items-center justify-between gap-3">
								<h3 class="font-semibold">Report #{report.id}</h3>
								<span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-700">dismissed</span>
							</div>
							<p class="text-sm text-slate-600">{report.reporter_name} → {report.admin_name}</p>
							<p class="text-xs text-slate-500">Submitted {formatReportDate(report.created_at)}</p>
							{#if report.item}
								<div class="flex items-center gap-3 rounded-[1rem] bg-white/80 p-3">
									{#if itemImageSrc(report.item)}
										<img
											alt={report.item.name}
											class="h-14 w-14 rounded-[0.9rem] object-cover"
											src={itemImageSrc(report.item) ?? undefined}
										/>
									{/if}
									<div>
										<p class="text-xs font-medium uppercase tracking-[0.14em] text-slate-500">Item</p>
										<p class="text-sm font-medium text-slate-900">{report.item.name}</p>
									</div>
								</div>
							{/if}
							<p class="text-sm text-slate-700"><span class="font-medium">Question:</span> {report.question_text_snapshot}</p>
							<p class="text-sm text-slate-700"><span class="font-medium">Reason:</span> {report.reason}</p>
						</article>
					{/each}
				{/if}
			</div>
		</section>
	</div>

	<Pagination basePath="/superadmin/reports" meta={data.reports.meta} />
</section>
