import { fail } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';
import type { Order, Paginated } from '$lib/types';

export const load = async (event) => {
	requireUser(event, ['admin']);
	const page = Number(event.url.searchParams.get('page') ?? '1');
	const search = event.url.searchParams.get('search')?.trim() ?? '';
	const sort = event.url.searchParams.get('sort')?.trim() ?? 'newest';
	const queue = event.url.searchParams.get('queue')?.trim() ?? 'shipping';
	const refundReasons = event.url.searchParams.getAll('refund_reasons').filter(Boolean);
	const params = new URLSearchParams({
		page: String(page),
		sort,
		queue
	});

	if (search) {
		params.set('search', search);
	}

	for (const refundReason of refundReasons) {
		params.append('refund_reasons', refundReason);
	}

	const orders = await backend<Paginated<Order>>(event, `/admin/orders?${params.toString()}`);

	return {
		orders,
		filters: {
			search,
			sort,
			queue,
			refundReasons
		}
	};
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
};
