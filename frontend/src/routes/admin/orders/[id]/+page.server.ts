import { fail } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';
import type { Order } from '$lib/types';

export const load = async (event) => {
	requireUser(event, ['admin']);
	const response = await backend<{ order: Order }>(event, `/admin/orders/${event.params.id}`);

	return {
		order: response.order
	};
};

export const actions = {
	ship: async (event) => {
		requireUser(event, ['admin']);

		try {
			await backend(event, `/admin/orders/${event.params.id}/ship`, { method: 'PATCH' });
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to ship order.') });
		}

		return { success: 'Order shipped.' };
	},
	refund: async (event) => {
		requireUser(event, ['admin']);
		const form = await event.request.formData();

		try {
			await backend(event, `/admin/orders/${event.params.id}/approve-refund`, {
				method: 'PATCH',
				body: {
					order_item_id: Number(form.get('order_item_id'))
				}
			});
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to approve refund.') });
		}

		return { success: 'Refund approved for the selected item.' };
	}
};
