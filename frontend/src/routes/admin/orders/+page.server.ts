import { fail } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';
import type { Order, Paginated } from '$lib/types';

export const load = async (event) => {
	requireUser(event, ['admin']);
	const page = Number(event.url.searchParams.get('page') ?? '1');
	const orders = await backend<Paginated<Order>>(event, `/admin/orders?page=${page}`);

	return { orders };
};

export const actions = {
	ship: async (event) => {
		requireUser(event, ['admin']);
		const form = await event.request.formData();

		try {
			await backend(event, `/admin/orders/${form.get('order_id')}/ship`, { method: 'PATCH' });
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to ship order.') });
		}

		return { success: 'Order shipped.' };
	},
	refund: async (event) => {
		requireUser(event, ['admin']);
		const form = await event.request.formData();

		try {
			await backend(event, `/admin/orders/${form.get('order_id')}/approve-refund`, { method: 'PATCH' });
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to approve refund.') });
		}

		return { success: 'Refund approved.' };
	}
};
