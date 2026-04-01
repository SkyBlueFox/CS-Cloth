import { fail } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';
import type { Address } from '$lib/types';

export const load = async (event) => {
	requireUser(event);

	const addresses =
		event.locals.user?.role === 'user'
			? await backend<{ data: Address[] }>(event, '/addresses')
			: { data: [] };

	return {
		user: event.locals.user,
		addresses: addresses.data
	};
};

export const actions = {
	updateProfile: async (event) => {
		const user = requireUser(event);

		if (user.role === 'admin') {
			return fail(403, {
				error: 'Admins cannot update their profile.'
			});
		}

		const form = await event.request.formData();

		try {
			await backend(event, '/auth/profile', {
				method: 'PATCH',
				body: {
					name: String(form.get('name') ?? ''),
					email: String(form.get('email') ?? ''),
					phone: String(form.get('phone') ?? ''),
					password: String(form.get('password') ?? ''),
					password_confirmation: String(form.get('password_confirmation') ?? '')
				}
			});
		} catch (error) {
			return fail(422, {
				error: getErrorMessage(error, 'Unable to update profile.')
			});
		}

		return {
			success: 'Profile updated.'
		};
	},
	saveAddress: async (event) => {
		requireUser(event, ['user']);
		const form = await event.request.formData();
		const addressId = form.get('address_id');

		try {
			await backend(event, addressId ? `/addresses/${addressId}` : '/addresses', {
				method: addressId ? 'PATCH' : 'POST',
				body: {
					label: String(form.get('label') ?? ''),
					recipient_name: String(form.get('recipient_name') ?? ''),
					phone: String(form.get('phone') ?? ''),
					line_1: String(form.get('line_1') ?? ''),
					line_2: String(form.get('line_2') ?? ''),
					district: String(form.get('district') ?? ''),
					province: String(form.get('province') ?? ''),
					postal_code: String(form.get('postal_code') ?? ''),
					country: String(form.get('country') ?? 'Thailand'),
					is_default: form.get('is_default') === '1'
				}
			});
		} catch (error) {
			return fail(422, {
				addressError: getErrorMessage(error, 'Unable to save address.')
			});
		}

		return {
			addressSuccess: 'Address saved.'
		};
	},
	deleteAddress: async (event) => {
		requireUser(event, ['user']);
		const form = await event.request.formData();

		try {
			await backend(event, `/addresses/${form.get('address_id')}`, {
				method: 'DELETE'
			});
		} catch (error) {
			return fail(422, {
				addressError: getErrorMessage(error, 'Unable to delete address.')
			});
		}

		return {
			addressSuccess: 'Address deleted.'
		};
	}
};
