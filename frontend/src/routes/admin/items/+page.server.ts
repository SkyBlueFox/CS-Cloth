import { fail } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';
import type { Item, Paginated } from '$lib/types';

export const load = async (event) => {
	requireUser(event, ['admin']);
	const page = Number(event.url.searchParams.get('page') ?? '1');
	const items = await backend<Paginated<Item>>(event, `/admin/items?page=${page}`);

	return {
		items
	};
};

export const actions = {
	toggle: async (event) => {
		requireUser(event, ['admin']);
		const form = await event.request.formData();
		await backend(event, `/admin/items/${form.get('item_id')}/toggle`, { method: 'PATCH' });
		return { success: 'Item status updated.' };
	},
	delete: async (event) => {
		requireUser(event, ['admin']);
		const form = await event.request.formData();

		try {
			await backend(event, `/admin/items/${form.get('item_id')}`, { method: 'DELETE' });
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to delete item.') });
		}

		return { success: 'Item deleted.' };
	}
};
