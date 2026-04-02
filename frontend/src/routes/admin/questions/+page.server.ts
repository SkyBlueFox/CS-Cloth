import { fail } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';
import type { Paginated, Question } from '$lib/types';

export const load = async (event) => {
	requireUser(event, ['admin']);
	const pendingPage = Number(event.url.searchParams.get('pending_page') ?? '1');
	const answeredPage = Number(event.url.searchParams.get('answered_page') ?? '1');
	const data = await backend<{ pending: Paginated<Question>; answered: Paginated<Question> }>(
		event,
		`/admin/questions?pending_page=${pendingPage}&answered_page=${answeredPage}`
	);

	return data;
};

export const actions = {
	answer: async (event) => {
		requireUser(event, ['admin']);
		const form = await event.request.formData();

		try {
			await backend(event, `/admin/questions/${form.get('question_id')}/answer`, {
				method: 'PATCH',
				body: {
					answer_text: String(form.get('answer_text') ?? '')
				}
			});
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to answer question.') });
		}

		return { success: 'Question answered.' };
	},
	delete: async (event) => {
		requireUser(event, ['admin']);
		const form = await event.request.formData();

		try {
			await backend(event, `/admin/questions/${form.get('question_id')}/answer`, { method: 'DELETE' });
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to delete answer.') });
		}

		return { success: 'Answer deleted.' };
	}
};
