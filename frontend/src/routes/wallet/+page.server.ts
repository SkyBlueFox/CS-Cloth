import { fail } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';
import type { Paginated, WalletTransaction } from '$lib/types';

type WalletResponse = {
	balance: number;
	transactions: Paginated<WalletTransaction>;
};

export const load = async (event) => {
	requireUser(event, ['user']);

	const wallet = await backend<WalletResponse>(event, '/wallet');

	return wallet;
};

export const actions = {
	topUp: async (event) => {
		requireUser(event, ['user']);
		const form = await event.request.formData();

		const presetAmount = String(form.get('preset_amount') ?? '').trim();
		const customAmount = String(form.get('custom_amount') ?? '').trim();
		const provider = String(form.get('provider') ?? 'scb_easy').trim();
		const cardholderName = String(form.get('cardholder_name') ?? '').trim();
		const cardNumber = String(form.get('card_number') ?? '').trim();
		const expiryDate = String(form.get('expiry_date') ?? '').trim();
		const cvv = String(form.get('cvv') ?? '').trim();
		const amount = customAmount !== '' ? customAmount : presetAmount;

		if (!amount) {
			return fail(422, {
				error: 'Choose a top-up amount.',
				values: {
					preset_amount: presetAmount,
					custom_amount: customAmount,
					provider,
					cardholder_name: cardholderName,
					card_number: cardNumber,
					expiry_date: expiryDate,
					cvv
				}
			});
		}

		try {
			const response = await backend<{ message: string }>(event, '/wallet/top-up', {
				method: 'POST',
				body: {
					amount,
					provider,
					cardholder_name: cardholderName,
					card_number: cardNumber,
					expiry_date: expiryDate,
					cvv
				}
			});

			return { success: response.message };
		} catch (error) {
			return fail(422, {
				error: getErrorMessage(error, 'Unable to top up wallet.'),
				values: {
					preset_amount: presetAmount,
					custom_amount: customAmount,
					provider,
					cardholder_name: cardholderName,
					card_number: cardNumber,
					expiry_date: expiryDate,
					cvv
				}
			});
		}
	}
};
