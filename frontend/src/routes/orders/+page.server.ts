import { fail } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';
import type { Order, Paginated } from '$lib/types';

export const load = async (event) => {
	requireUser(event, ['user']);
	const page = Number(event.url.searchParams.get('page') ?? '1');
	const orders = await backend<Paginated<Order>>(event, `/orders?page=${page}`);

	return {
		orders
	};
};

export const actions = {
	cancel: async (event) => {
		requireUser(event, ['user']);
		const form = await event.request.formData();

		try {
			await backend(event, `/orders/${form.get('order_id')}/cancel`, { method: 'POST' });
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to cancel order.') });
		}

		return { success: 'Order cancelled.' };
	},
	refund: async (event) => {
		requireUser(event, ['user']);
		const form = await event.request.formData();

		try {
			await backend(event, `/orders/${form.get('order_id')}/refund`, { method: 'POST' });
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to request refund.') });
		}

		return { success: 'Refund requested.' };
	}
};