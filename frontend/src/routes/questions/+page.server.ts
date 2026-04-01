import { fail } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';
import type { Paginated, Question } from '$lib/types';

export const load = async (event) => {
	requireUser(event, ['user']);
	const page = Number(event.url.searchParams.get('page') ?? '1');
	const search = event.url.searchParams.get('search')?.trim() ?? '';
	const params = new URLSearchParams({ page: String(page) });

	if (search) {
		params.set('search', search);
	}

	const questions = await backend<Paginated<Question>>(event, `/questions?${params.toString()}`);

	return {
		questions,
		filters: {
			search
		}
	};
};

export const actions = {
	report: async (event) => {
		requireUser(event, ['user']);
		const form = await event.request.formData();

		try {
			await backend(event, `/questions/${form.get('question_id')}/report`, {
				method: 'POST',
				body: {
					reason: String(form.get('reason') ?? '')
				}
			});
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to submit report.') });
		}

		return { success: 'Report submitted.' };
	}
};
