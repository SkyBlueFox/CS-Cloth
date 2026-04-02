<script lang="ts">
	import Pagination from '$lib/components/Pagination.svelte';
	import type { Question } from '$lib/types';
	import { itemImageSrc } from '$lib/media';

	let { data, form } = $props();
	let search = $state('');
	let sort = $state<'az' | 'za' | 'newest' | 'oldest'>('newest');

	const matchesSearch = (question: Question, term: string) => {
		const query = term.trim().toLowerCase();
		if (!query) {
			return true;
		}

		return [
			question.item?.name ?? '',
			question.asker_name,
			question.question_text,
			question.answer_text ?? ''
		].some((value) => value.toLowerCase().includes(query));
	};

	const compareQuestions = (left: Question, right: Question) => {
		if (sort === 'az') {
			return (left.item?.name ?? '').localeCompare(right.item?.name ?? '');
		}

		if (sort === 'za') {
			return (right.item?.name ?? '').localeCompare(left.item?.name ?? '');
		}

		const leftTime = Date.parse(left.created_at ?? '') || 0;
		const rightTime = Date.parse(right.created_at ?? '') || 0;

		return sort === 'oldest' ? leftTime - rightTime : rightTime - leftTime;
	};

	const visiblePending = $derived(
		[...data.pending.data].filter((question) => matchesSearch(question, search)).sort(compareQuestions)
	);
	const visibleAnswered = $derived(
		[...data.answered.data].filter((question) => matchesSearch(question, search)).sort(compareQuestions)
	);

	function formatDate(value: string | null) {
		if (!value) return 'Unknown time';

		return new Intl.DateTimeFormat('en-GB', {
			dateStyle: 'medium',
			timeStyle: 'short'
		}).format(new Date(value));
	}
</script>

<section class="space-y-8">
	<div class="panel space-y-5">
		<div>
			<p class="eyebrow">Admin</p>
			<h1 class="mt-2 text-3xl font-semibold">Question moderation</h1>
		</div>
		<div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_16rem]">
			<input bind:value={search} class="rounded-2xl border-slate-300" placeholder="Search by item, asker, question, or answer" />
			<select bind:value={sort} class="rounded-2xl border-slate-300">
				<option value="newest">Newest first</option>
				<option value="oldest">Oldest first</option>
				<option value="az">Item A-Z</option>
				<option value="za">Item Z-A</option>
			</select>
		</div>
	</div>
	{#if form?.error}
		<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
	{/if}
	{#if form?.success}
		<p class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{form.success}</p>
	{/if}

	<div class="grid gap-8 xl:grid-cols-2">
		<div class="space-y-5">
			<div>
				<p class="eyebrow">Pending</p>
				<h2 class="mt-2 text-2xl font-semibold">Needs an answer</h2>
			</div>
			{#if visiblePending.length}
				{#each visiblePending as question}
				<article class="panel space-y-4">
					<a class="flex items-center gap-4 rounded-2xl bg-slate-50 p-4 transition hover:bg-white hover:ring-1 hover:ring-blue-200" href={question.item ? `/items/${question.item.id}` : undefined}>
						{#if question.item && itemImageSrc(question.item)}
							<img alt={question.item.name} class="h-16 w-16 rounded-[1rem] object-cover" src={itemImageSrc(question.item) ?? undefined} />
						{:else}
							<div class="subtle-box flex h-16 w-16 items-center justify-center text-xs text-sky-500">No image</div>
						{/if}
						<div>
							<p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Item</p>
							<p class="font-medium text-slate-900">{question.item?.name ?? `Item #${question.item_id}`}</p>
						</div>
					</a>
					<div class="text-sm text-slate-500">
						<p>Asked by {question.asker_name}</p>
						<p>Asked on {formatDate(question.created_at)}</p>
					</div>
					<p>{question.question_text}</p>
					<form class="space-y-3" method="POST" action="?/answer">
						<input name="question_id" type="hidden" value={question.id} />
						<textarea class="w-full rounded-2xl border-slate-300" name="answer_text" rows="4" required></textarea>
						<button class="btn-primary" type="submit">Submit answer</button>
					</form>
				</article>
				{/each}
			{:else}
				<div class="panel text-sm text-slate-600">No pending questions match the current search.</div>
			{/if}
			<Pagination basePath="/admin/questions" meta={data.pending.meta} paramName="pending_page" />
		</div>

		<div class="space-y-5">
			<div>
				<p class="eyebrow">Answered</p>
				<h2 class="mt-2 text-2xl font-semibold">Answered by you</h2>
			</div>
			{#if visibleAnswered.length}
				{#each visibleAnswered as question}
				<article class="panel space-y-4">
					<a class="flex items-center gap-4 rounded-2xl bg-slate-50 p-4 transition hover:bg-white hover:ring-1 hover:ring-blue-200" href={question.item ? `/items/${question.item.id}` : undefined}>
						{#if question.item && itemImageSrc(question.item)}
							<img alt={question.item.name} class="h-16 w-16 rounded-[1rem] object-cover" src={itemImageSrc(question.item) ?? undefined} />
						{:else}
							<div class="subtle-box flex h-16 w-16 items-center justify-center text-xs text-sky-500">No image</div>
						{/if}
						<div>
							<p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Item</p>
							<p class="font-medium text-slate-900">{question.item?.name ?? `Item #${question.item_id}`}</p>
						</div>
					</a>
					<div class="text-sm text-slate-500">
						<p>Asked by {question.asker_name}</p>
						<p>Asked on {formatDate(question.created_at)}</p>
					</div>
					<p>{question.question_text}</p>
					<div class="rounded-[1.25rem] bg-sky-50 p-4">{question.answer_text}</div>
					<form method="POST" action="?/delete">
						<input name="question_id" type="hidden" value={question.id} />
						<button class="rounded-full border border-rose-300 px-4 py-2 text-sm text-rose-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-rose-600 hover:bg-rose-600 hover:text-white hover:shadow-lg hover:shadow-rose-200" type="submit">Delete answer</button>
					</form>
				</article>
				{/each}
			{:else}
				<div class="panel text-sm text-slate-600">No answered questions match the current search.</div>
			{/if}
			<Pagination basePath="/admin/questions" meta={data.answered.meta} paramName="answered_page" />
		</div>
	</div>
</section>
