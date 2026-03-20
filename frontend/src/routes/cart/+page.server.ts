import { backend, getErrorMessage } from '$lib/server/backend';
import { fail } from '@sveltejs/kit';
import type { CartItem } from '$lib/types';

export const load = async (event) => {
	if (!event.locals.user) {
		return { cart: [] as CartItem[] };
	}

	try {
		const response = await backend<{ data: CartItem[] }>(event, '/cart');
		return { cart: response.data };
	} catch (error) {
		return fail(422, { error: getErrorMessage(error, 'Unable to fetch cart.') });
	}
};

export const actions = {
	remove: async (event) => {
		const form = await event.request.formData();
		const itemId = form.get('item_id');

		try {
			await backend(event, `/cart/${itemId}`, { method: 'DELETE' });
			return { success: 'Item removed from cart.' };
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to remove item.') });
		}
	},
	update: async (event) => {
		const form = await event.request.formData();
		const itemId = form.get('item_id');
		const quantity = Number(form.get('quantity') ?? 1);

		try {
			await backend(event, `/cart/${itemId}`, {
				method: 'PUT',
				body: { quantity }
			});
			return { success: 'Cart updated.' };
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to update cart.') });
		}
	}
};