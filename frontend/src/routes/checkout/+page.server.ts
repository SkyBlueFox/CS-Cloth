import { backend, getErrorMessage } from '$lib/server/backend';
import { fail } from '@sveltejs/kit';
import type { RequestEvent } from '@sveltejs/kit';
import type { Address, CartItem } from '$lib/types';

export const load = async (event) => {
	if (!event.locals.user) {
		return { cart: [] as CartItem[], addresses: [] as Address[] };
	}

	const cartResp = await backend<{ data: CartItem[] }>(event, '/cart');
	const addrResp =
		event.locals.user.role === 'user'
			? await backend<{ data: Address[] }>(event, '/addresses')
			: { data: [] };

	return {
		cart: cartResp.data,
		addresses: addrResp.data
	};
};

export const actions = {
	placeOrder: async (event: RequestEvent) => {
		const form = await event.request.formData();
		const addressId = form.get('address_id');
		const cartItems = form.getAll('cart_item_id');

		try {
			await backend(event, '/checkout', {
				method: 'POST',
				body: {
					cart_items: cartItems.map(Number),
					address_id: addressId ? Number(addressId) : null
				}
			});
			return { success: 'Order placed successfully.' };
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to place order.') });
		}
	}
};