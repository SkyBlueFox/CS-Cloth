import { backend } from '$lib/server/backend';
import { clearAuthToken } from '$lib/server/session';
import { redirect } from '@sveltejs/kit';
import type { RequestHandler } from './$types';

export const POST: RequestHandler = async ({ cookies, fetch, locals }) => {
	try {
		if (locals.authToken) {
			await backend({ fetch, locals }, '/auth/logout', { method: 'POST' });
		}
	} catch {
		// ignore
	}

	clearAuthToken(cookies);

	throw redirect(303, '/login');
};