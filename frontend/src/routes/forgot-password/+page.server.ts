import { fail, redirect } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { landingFor } from '$lib/server/auth';

export const load = async ({ locals }) => {
	if (locals.user) {
		throw redirect(303, landingFor(locals.user));
	}
};

export const actions = {
	requestOtp: async (event) => {
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
				error: getErrorMessage(error, 'Unable to send OTP.'),
				email: String(form.get('email') ?? ''),
				otpSent: false
			});
		}

		return {
			success: 'OTP sent to your email.',
			email: String(form.get('email') ?? ''),
			otpSent: true
		};
	},
	resetPassword: async (event) => {
		const form = await event.request.formData();

		try {
			await backend(event, '/auth/reset-password', {
				body: {
					email: String(form.get('email') ?? ''),
					otp: String(form.get('otp') ?? ''),
					password: String(form.get('password') ?? ''),
					password_confirmation: String(form.get('password_confirmation') ?? '')
				},
				auth: false
			});
		} catch (error) {
			return fail(422, {
				error: getErrorMessage(error, 'Unable to reset password.'),
				email: String(form.get('email') ?? ''),
				otpSent: true
			});
		}

		throw redirect(303, '/login');
	}
};
