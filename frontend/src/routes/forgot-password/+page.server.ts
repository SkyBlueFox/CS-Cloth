import { fail, redirect } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { landingFor } from '$lib/server/auth';

export const load = async ({ locals }) => {
	if (locals.user) {
		throw redirect(303, landingFor(locals.user));
	}
};

export const actions = {
	default: async (event) => {
		const form = await event.request.formData();

		try {
			await backend(event, '/auth/forgot-password', {
				body: {
					email: String(form.get('email') ?? '')
				},
				auth: false
			});
		} catch (error) {
			return fail(422, {
				error: getErrorMessage(error, 'Unable to send reset link.'),
				email: String(form.get('email') ?? '')
			});
		}

		return {
			success: 'If that email exists, a password reset link has been sent.',
			email: String(form.get('email') ?? '')
		};
	}
};
