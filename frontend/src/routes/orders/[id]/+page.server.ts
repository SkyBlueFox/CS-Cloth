import { fail } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';
import type { Order } from '$lib/types';

export const load = async (event) => {
	requireUser(event, ['user']);
	const response = await backend<{ order: Order }>(event, `/orders/${event.params.id}`);

	return {
		order: response.order
	};
};

export const actions = {
	cancel: async (event) => {
		requireUser(event, ['user']);

		try {
			await backend(event, `/orders/${event.params.id}/cancel`, { method: 'PATCH' });
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to cancel order.') });
		}

		return { success: 'Order cancelled.' };
	},
	refund: async (event) => {
		requireUser(event, ['user']);
		const form = await event.request.formData();

		try {
			await backend(event, `/orders/${event.params.id}/refund`, { method: 'PATCH', body: form });
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to request refund.') });
		}

		return { success: 'Refund requested.' };
	}
};
