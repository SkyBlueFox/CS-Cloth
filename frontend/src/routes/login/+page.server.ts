import { fail, redirect, isRedirect } from '@sveltejs/kit';
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

			console.log('--- LOGIN ACTION DEBUG ---');
			console.log('Full API Response:', response);
			console.log('Extracted Token:', response.data.token)

			setAuthToken(event.cookies, response.data.token);

			throw redirect(303, '/');
		} catch (error) {
			// 👇 If it's a redirect, let it through!
			if (isRedirect(error)) throw error;

			return fail(422, {
				error: getErrorMessage(error, 'Unable to login.'),
				email: String(form.get('email') ?? '')
			});
		}

		setAuthToken(event.cookies, token);
		throw redirect(303, '/');
	}
};