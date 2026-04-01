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
		const submittedName = String(form.get('name') ?? '');
		const submittedEmail = String(form.get('email') ?? '');
		const submittedPhone = String(form.get('phone') ?? '');

		try {
			const response = await backend<{ message?: string; data?: { user?: { pending_email_change?: string | null } } }>(event, '/auth/profile', {
				method: 'PATCH',
				body: {
					name: submittedName,
					email: submittedEmail,
					phone: submittedPhone,
					password: String(form.get('password') ?? ''),
					password_confirmation: String(form.get('password_confirmation') ?? '')
				}
			});

			const pendingEmailChange = response.data?.user?.pending_email_change ?? null;
			const message = response.message ?? (pendingEmailChange
				? 'Profile updated. Enter the OTP sent to your new email.'
				: 'Profile updated.');

			return {
				success: message,
				pendingEmailChange,
				name: submittedName,
				email: submittedEmail,
				phone: submittedPhone
			};
		} catch (error) {
			return fail(422, {
				error: getErrorMessage(error, 'Unable to update profile.'),
				pendingEmailChange: submittedEmail !== String(user.email ?? '') ? submittedEmail : null,
				name: submittedName,
				email: submittedEmail,
				phone: submittedPhone
			});
		}
	},
	confirmEmailChange: async (event) => {
		requireUser(event, ['user']);
		const form = await event.request.formData();
		const updatedEmail = event.locals.user?.pending_email_change ?? event.locals.user?.email ?? null;

		try {
			await backend(event, '/auth/profile/confirm-email-change', {
				body: {
					otp: String(form.get('otp') ?? '')
				}
			});
		} catch (error) {
			return fail(422, {
				error: getErrorMessage(error, 'Unable to confirm email change.'),
				pendingEmailChange: event.locals.user?.pending_email_change ?? null
			});
		}

		return {
			success: 'Email updated.',
			pendingEmailChange: null,
			email: updatedEmail
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
