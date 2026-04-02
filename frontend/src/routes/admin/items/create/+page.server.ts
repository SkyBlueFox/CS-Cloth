import { fail, redirect } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';

export const load = async (event) => {
	requireUser(event, ['admin']);
};

export const actions = {
	default: async (event) => {
		requireUser(event, ['admin']);
		const form = await event.request.formData();

		try {
			await backend(event, '/admin/items', {
				body: form
			});
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to create item.') });
		}

		redirect(303, '/admin/items');
	}
};
