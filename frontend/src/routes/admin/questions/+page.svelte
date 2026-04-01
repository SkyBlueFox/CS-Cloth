<script lang="ts">
	import Pagination from '$lib/components/Pagination.svelte';

	let { data, form } = $props();
</script>

<section class="space-y-8">
	<div class="panel">
		<p class="eyebrow">Admin</p>
		<h1 class="mt-2 text-3xl font-semibold">Question moderation</h1>
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
			{#each data.pending.data as question}
				<article class="panel space-y-4">
					<p class="font-medium">{question.item?.name}</p>
					<p class="text-sm text-slate-500">Asked by {question.asker_name}</p>
					<p>{question.question_text}</p>
					<form class="space-y-3" method="POST" action="?/answer">
						<input name="question_id" type="hidden" value={question.id} />
						<textarea class="w-full rounded-2xl border-slate-300" name="answer_text" rows="4" required></textarea>
						<button class="btn-primary" type="submit">Submit answer</button>
					</form>
				</article>
			{/each}
			<Pagination basePath="/admin/questions" meta={data.pending.meta} paramName="pending_page" />
		</div>

		<div class="space-y-5">
			<div>
				<p class="eyebrow">Answered</p>
				<h2 class="mt-2 text-2xl font-semibold">Answered by you</h2>
			</div>
			{#each data.answered.data as question}
				<article class="panel space-y-4">
					<p class="font-medium">{question.item?.name}</p>
					<p>{question.question_text}</p>
					<div class="rounded-[1.25rem] bg-sky-50 p-4">{question.answer_text}</div>
					<form method="POST" action="?/delete">
						<input name="question_id" type="hidden" value={question.id} />
						<button class="rounded-full border border-rose-300 px-4 py-2 text-sm text-rose-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-rose-600 hover:bg-rose-600 hover:text-white hover:shadow-lg hover:shadow-rose-200" type="submit">Delete answer</button>
					</form>
				</article>
			{/each}
			<Pagination basePath="/admin/questions" meta={data.answered.meta} paramName="answered_page" />
		</div>
	</div>
</section>
