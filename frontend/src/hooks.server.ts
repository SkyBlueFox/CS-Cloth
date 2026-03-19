import { clearAuthToken, getAuthToken } from '$lib/server/session';
import { backend, isApiError } from '$lib/server/backend';
import type { Handle } from '@sveltejs/kit';

export const handle: Handle = async ({ event, resolve }) => {
	event.locals.authToken = getAuthToken(event.cookies);
	event.locals.user = null;

	if (event.locals.authToken) {
		try {
			const response = await backend<{ user: App.Locals['user'] }>(event, '/auth/me');
			event.locals.user = response.user;
		} catch (error) {
			if (isApiError(error) && error.status === 401) {
				clearAuthToken(event.cookies);
				event.locals.authToken = null;
			} else {
				throw error;
			}
		}
	}

	return resolve(event);
};
