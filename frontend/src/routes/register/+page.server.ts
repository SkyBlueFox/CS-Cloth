import { fail, isRedirect, redirect } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { landingFor } from '$lib/server/auth';
import { setAuthToken } from '$lib/server/session';
import type { Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ locals }) => {
    if (locals.user) {
        throw redirect(303, landingFor(locals.user));
    }
};

export const actions: Actions = {
    requestOtp: async (event) => {
        const form = await event.request.formData();
        const name = String(form.get('name') ?? '');
        const email = String(form.get('email') ?? '');
        const password = String(form.get('password') ?? '');
        const password_confirmation = String(form.get('password_confirmation') ?? '');

        try {
            await backend(event, '/auth/register/request-otp', {
                method: 'POST',
                body: { name, email, password, password_confirmation },
                auth: false
            });

            return {
                success: 'OTP sent to your email.',
                otpSent: true,
                name,
                email
            };
        } catch (error) {
            return fail(422, {
                error: getErrorMessage(error, 'Unable to send OTP.'),
                name,
                email,
                otpSent: false
            });
        }
    },
    verifyOtp: async (event) => {
        const form = await event.request.formData();
        const email = String(form.get('email') ?? '');
        const otp = String(form.get('otp') ?? '');

        try {
            const response = await backend<{ data: { token: string } }>(event, '/auth/register/verify-otp', {
                method: 'POST',
                body: { email, otp },
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
                email
            });
        }
    }
};