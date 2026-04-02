import { fail, redirect } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';
import type { Item } from '$lib/types';

export const load = async (event) => {
	requireUser(event, ['admin']);
	const { item } = await backend<{ item: Item }>(event, `/admin/items/${event.params.id}`);

	return { item };
};

export const actions = {
	default: async (event) => {
		requireUser(event, ['admin']);
		const form = await event.request.formData();

		try {
			await backend(event, `/admin/items/${event.params.id}`, {
				method: 'PATCH',
				body: form
			});
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to update item.') });
		}

		redirect(303, '/admin/items');
	}
};
