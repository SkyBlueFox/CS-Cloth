import { fail, redirect } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { landingFor } from '$lib/server/auth';

export const load = async ({ locals, params, url }) => {
	if (locals.user) {
		throw redirect(303, landingFor(locals.user));
	}

	return {
		token: params.token,
		email: url.searchParams.get('email') ?? ''
	};
};

export const actions = {
	default: async (event) => {
		const form = await event.request.formData();

		try {
			await backend(event, '/auth/reset-password', {
				body: {
					token: String(form.get('token') ?? ''),
					email: String(form.get('email') ?? ''),
					password: String(form.get('password') ?? ''),
					password_confirmation: String(form.get('password_confirmation') ?? '')
				},
				auth: false
			});
		} catch (error) {
			return fail(422, {
				error: getErrorMessage(error, 'Unable to reset password.'),
				email: String(form.get('email') ?? ''),
				token: String(form.get('token') ?? '')
			});
		}

		throw redirect(303, '/login');
	}
};
