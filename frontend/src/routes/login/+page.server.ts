import { fail, redirect } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { landingFor } from '$lib/server/auth';
import { setAuthToken } from '$lib/server/session';

export const load = async ({ locals }) => {
	if (locals.user) {
		throw redirect(303, landingFor(locals.user));
	}
};

export const actions = {
	default: async (event) => {
		const form = await event.request.formData();

		let token: string;

		try {
			const response = await backend<{ data: { token: string } }>(event, '/auth/login', {
				body: {
					email: String(form.get('email') ?? ''),
					password: String(form.get('password') ?? '')
				},
				auth: false
			});

			token = response.data.token;
		} catch (error) {
			return fail(422, {
				error: getErrorMessage(error, 'Unable to login.'),
				email: String(form.get('email') ?? '')
			});
		}

		setAuthToken(event.cookies, token);
		throw redirect(303, '/');
	}
};