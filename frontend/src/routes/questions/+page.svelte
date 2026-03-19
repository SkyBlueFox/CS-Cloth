<script lang="ts">
	import Pagination from '$lib/components/Pagination.svelte';

	let { data, form } = $props();
</script>

<section class="space-y-6">
	<div class="panel">
		<p class="eyebrow">Questions</p>
		<h1 class="mt-2 text-3xl font-semibold">Track your item questions</h1>
	</div>
	{#if form?.error}
		<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{form.error}</p>
	{/if}
	{#if form?.success}
		<p class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{form.success}</p>
	{/if}
	<div class="space-y-5">
		{#each data.questions.data as question}
			<article class="panel space-y-4">
				<div class="flex items-center justify-between gap-4">
					<div>
						<h2 class="text-xl font-semibold">{question.item?.name ?? 'Deleted item'}</h2>
						<p class="text-sm text-slate-500">{question.created_at}</p>
					</div>
				</div>
				<div class="rounded-[1.25rem] bg-slate-50 p-4">
					<p class="text-sm font-medium text-slate-600">Question</p>
					<p class="mt-2">{question.question_text}</p>
				</div>
				{#if question.answer_text}
					<div class="rounded-[1.25rem] bg-emerald-50 p-4">
						<p class="text-sm font-medium text-emerald-700">Answer from {question.admin_name}</p>
						<p class="mt-2">{question.answer_text}</p>
					</div>
					<form class="space-y-3" method="POST" action="?/report">
						<input name="question_id" type="hidden" value={question.id} />
						<label class="block">
							<span class="mb-1 block text-sm font-medium">Report this answer</span>
							<textarea class="w-full rounded-2xl border-slate-300" name="reason" rows="3" minlength="10" maxlength="255" required></textarea>
						</label>
						<button class="rounded-full border border-rose-300 px-4 py-2 text-sm text-rose-700" type="submit">Submit report</button>
					</form>
				{:else}
					<p class="text-sm text-amber-700">Still waiting for an answer.</p>
				{/if}
			</article>
		{/each}
	</div>
	<Pagination basePath="/questions" meta={data.questions.meta} />
</section>
