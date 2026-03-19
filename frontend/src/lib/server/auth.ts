import { redirect } from '@sveltejs/kit';
import type { RequestEvent } from '@sveltejs/kit';
import type { Role, User } from '$lib/types';

export function landingFor(user: User | null) {
	if (!user) {
		return '/shop';
	}

	if (user.role === 'superadmin') {
		return '/superadmin/reports';
	}

	if (user.role === 'admin') {
		return '/admin/items';
	}

	return '/shop';
}

export function requireUser(event: RequestEvent, roles?: Role[]) {
	if (!event.locals.user) {
		redirect(303, '/login');
	}

	if (roles && !roles.includes(event.locals.user.role)) {
		redirect(303, landingFor(event.locals.user));
	}

	return event.locals.user;
}
