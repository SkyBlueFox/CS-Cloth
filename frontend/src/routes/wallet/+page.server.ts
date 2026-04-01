import { fail, type Actions } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';
import type { Paginated, User, WalletTransaction } from '$lib/types';
import type { PageServerLoad } from '../$types';

// Define the WalletResponse interface based on the actual API response
interface WalletResponse {
    balance: number;
    transactions: Paginated<WalletTransaction>;
}

export const load: PageServerLoad = async (event) => {
    // Verify user access permissions
    requireUser(event, ['user']);

    try {
        // Fetch wallet data from Backend
        // If the backend function throws an error when response.ok is false,
        // it will jump directly to the catch block immediately without causing a 500 error
        const wallet = await backend<WalletResponse>(event, '/wallet');

        return {
            wallet: wallet || { balance: 0, transactions: { data: [], meta: { total: 0, current_page: 1, last_page: 1, per_page: 1 } } }
        };
    } catch (err) {
        console.error('Wallet Load Error:', err);
        
        // Instead of allowing a 500 error, return default values for the UI to display
        return {
            wallet: { balance: 0, transactions: { data: [], meta: { total: 0, current_page: 1, last_page: 1, per_page: 1 } } },
            error: getErrorMessage(err, 'Unable to load wallet information')
        };
    }
};

export const actions: Actions = {
    topUp: async (event) => {
        requireUser(event, ['user']);
        const form = await event.request.formData();

        const presetAmount = form.get('preset_amount')?.toString() || '';
        const customAmount = form.get('custom_amount')?.toString() || '';
        const provider = form.get('provider')?.toString() || 'scb_easy';
        
        const cardholderName = form.get('cardholder_name')?.toString() || '';
        const cardNumber = form.get('card_number')?.toString() || '';
        const expiryDate = form.get('expiry_date')?.toString() || '';
        const cvv = form.get('cvv')?.toString() || '';

        // Handle amount determination
        const rawAmount = customAmount.trim() !== '' ? customAmount : presetAmount;
        const amount = parseFloat(rawAmount);

        const values = {
            preset_amount: presetAmount,
            custom_amount: customAmount,
            provider,
            cardholder_name: cardholderName,
            card_number: cardNumber,
            expiry_date: expiryDate,
            cvv
        };

        // Validate the amount value
        if (isNaN(amount) || amount <= 0) {
            return fail(422, {
                error: 'Please specify a valid amount to top up',
                values
            });
        }

		try {
			const response = await backend<{ message: string; user: User }>(event, '/wallet/top-up', {
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

			return {
				success: response.message,
				updatedUser: response.user
			};
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