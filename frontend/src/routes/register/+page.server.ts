import { fail, isRedirect, redirect } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { landingFor } from '$lib/server/auth';
import { setAuthToken } from '$lib/server/session';

export const load = async ({ locals }) => {
	if (locals.user) {
		throw redirect(303, landingFor(locals.user));
	}
};

export const actions = {
	requestOtp: async (event) => {
		const form = await event.request.formData();

		try {
			await backend(event, '/auth/register/request-otp', {
				body: {
					name: String(form.get('name') ?? ''),
					email: String(form.get('email') ?? ''),
					password: String(form.get('password') ?? ''),
					password_confirmation: String(form.get('password_confirmation') ?? '')
				},
				auth: false
			});
		} catch (error) {
			return fail(422, {
				error: getErrorMessage(error, 'Unable to send OTP.'),
				name: String(form.get('name') ?? ''),
				email: String(form.get('email') ?? ''),
				otpSent: false
			});
		}

		return {
			success: 'OTP sent to your email.',
			otpSent: true,
			name: String(form.get('name') ?? ''),
			email: String(form.get('email') ?? '')
		};
	},
	verifyOtp: async (event) => {
		const form = await event.request.formData();

		try {
			const response = await backend<{ data: { token: string } }>(event, '/auth/register/verify-otp', {
				body: {
					email: String(form.get('email') ?? ''),
					otp: String(form.get('otp') ?? '')
				},
				auth: false
			});

			setAuthToken(event.cookies, response.data.token);
			throw redirect(303, '/');
		} catch (error) {
			if (isRedirect(error)) throw error;

			return fail(422, {
				error: getErrorMessage(error, 'Unable to verify OTP.'),
				otpSent: true,
				name: String(form.get('name') ?? ''),
				email: String(form.get('email') ?? '')
			});
		}
	}
};
