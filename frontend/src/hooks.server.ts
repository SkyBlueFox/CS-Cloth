import { clearAuthToken, getAuthToken } from '$lib/server/session';
import { backend, isApiError } from '$lib/server/backend';
import { redirect, type Handle } from '@sveltejs/kit';
import type { User } from '$lib/types';

export const handle: Handle = async ({ event, resolve }) => {
	event.locals.authToken = getAuthToken(event.cookies);
	event.locals.user = null;

	console.log('--- HOOK DEBUG ---');
	console.log('Token from Cookie:', event.locals.authToken);

	const publicPaths = ['/login', '/register', '/forgot-password'];
	const isPublicPath = publicPaths.includes(event.url.pathname) || event.url.pathname.startsWith('/reset-password/');

	if (event.locals.authToken && !isPublicPath) {
		try {
			const response = await backend<{ data: { user: User } }>(event, '/auth/me');

			console.log('/auth/me response structure:', response);

			event.locals.user = response.data.user;
			console.log('User set to locals:', event.locals.user.name);
		} catch (error) {
			if (isApiError(error) && error.status === 401) {
				clearAuthToken(event.cookies);
				event.locals.authToken = null;
			} else {
				throw error;
			}
		}
	}

	// 🔐 auth guard
	const protectedPaths = ['/cart', '/checkout', '/orders', '/profile', '/wallet', '/questions'];

	if (!event.locals.user && protectedPaths.some(p => event.url.pathname.startsWith(p))) {
		throw redirect(303, '/login');
	}

	// 🔐 role guard
	if (event.url.pathname.startsWith('/admin') && event.locals.user?.role !== 'admin') {
		throw redirect(303, '/');
	}

	if (event.url.pathname.startsWith('/superadmin') && event.locals.user?.role !== 'superadmin') {
		throw redirect(303, '/');
	}

	return resolve(event, {
		filterSerializedResponseHeaders: (name) => name === 'content-type'
	});
};
