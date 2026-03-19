import { fail, redirect } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { landingFor } from '$lib/server/auth';
import { setAuthToken } from '$lib/server/session';

export const load = async ({ locals }) => {
	if (locals.user) {
		redirect(303, landingFor(locals.user));
	}
};

export const actions = {
	default: async (event) => {
		const form = await event.request.formData();

		try {
			const response = await backend<{ token: string }>(event, '/auth/register', {
				body: {
					name: String(form.get('name') ?? ''),
					email: String(form.get('email') ?? ''),
					password: String(form.get('password') ?? ''),
					password_confirmation: String(form.get('password_confirmation') ?? '')
				},
				auth: false
			});

			setAuthToken(event.cookies, response.token);
		} catch (error) {
			return fail(422, {
				error: getErrorMessage(error, 'Unable to register.'),
				name: String(form.get('name') ?? ''),
				email: String(form.get('email') ?? '')
			});
		}

		redirect(303, '/');
	}
};
