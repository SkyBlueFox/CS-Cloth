import { fail } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import type { Address, Item, Question } from '$lib/types';

export const load = async (event) => {
	const response = await backend<{ item: Item; questions: Question[] }>(
		event,
		`/shop/items/${event.params.id}`
	);

	const addresses =
		event.locals.user?.role === 'user'
			? await backend<{ data: Address[] }>(event, '/addresses')
			: { data: [] };

	return {
		...response,
		addresses: addresses.data
	};
};

export const actions = {
	question: async (event) => {
		const user = event.locals.user;
		if (!user || user.role !== 'user') {
			return fail(403, { error: 'Only normal users can ask questions.' });
		}

		const form = await event.request.formData();

		try {
			await backend(event, `/shop/items/${event.params.id}/questions`, {
				body: {
					question_text: String(form.get('question_text') ?? '')
				}
			});
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to submit question.') });
		}

		return { success: 'Question submitted.' };
	},
	report: async (event) => {
		const user = event.locals.user;
		if (!user || user.role !== 'user') {
			return fail(403, { error: 'Only normal users can report answers.' });
		}

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
	},
	order: async (event) => {
		const user = event.locals.user;
		if (!user || user.role !== 'user') {
			return fail(403, { error: 'Only normal users can place orders.' });
		}

		const form = await event.request.formData();

		try {
			const addressId = form.get('address_id');
			await backend(event, `/shop/items/${event.params.id}/order`, {
				body: {
					quantity: Number(form.get('quantity') ?? 1),
					address_id: addressId ? Number(addressId) : null,
					save_address: form.get('save_address') === '1',
					set_as_default: form.get('set_as_default') === '1',
					shipping_address: addressId
						? undefined
						: {
							label: String(form.get('label') ?? ''),
							recipient_name: String(form.get('recipient_name') ?? ''),
							phone: String(form.get('phone') ?? ''),
							line_1: String(form.get('line_1') ?? ''),
							line_2: String(form.get('line_2') ?? ''),
							district: String(form.get('district') ?? ''),
							province: String(form.get('province') ?? ''),
							postal_code: String(form.get('postal_code') ?? ''),
							country: String(form.get('country') ?? 'Thailand')
						}
				}
			});
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to place order.') });
		}

		return { success: 'Order placed.' };
	}
};