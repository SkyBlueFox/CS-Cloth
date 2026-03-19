import { fail } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';
import type { User } from '$lib/types';

export const load = async (event) => {
	requireUser(event, ['superadmin']);
	const admins = await backend<{ active: User[]; deactivated: User[] }>(event, '/superadmin/admins');
	return admins;
};

export const actions = {
	create: async (event) => {
		requireUser(event, ['superadmin']);
		const form = await event.request.formData();

		try {
			await backend(event, '/superadmin/admins', {
				body: {
					name: String(form.get('name') ?? ''),
					email: String(form.get('email') ?? ''),
					password: String(form.get('password') ?? ''),
					password_confirmation: String(form.get('password_confirmation') ?? '')
				}
			});
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to create admin.') });
		}

		return { success: 'Admin created.' };
	},
	update: async (event) => {
		requireUser(event, ['superadmin']);
		const form = await event.request.formData();

		try {
			await backend(event, `/superadmin/admins/${form.get('user_id')}`, {
				method: 'PATCH',
				body: {
					name: String(form.get('name') ?? ''),
					email: String(form.get('email') ?? ''),
					password: String(form.get('password') ?? ''),
					password_confirmation: String(form.get('password_confirmation') ?? '')
				}
			});
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to update admin.') });
		}

		return { success: 'Admin updated.' };
	},
	delete: async (event) => {
		requireUser(event, ['superadmin']);
		const form = await event.request.formData();

		try {
			await backend(event, `/superadmin/admins/${form.get('user_id')}`, { method: 'DELETE' });
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to deactivate admin.') });
		}

		return { success: 'Admin deactivated.' };
	},
	restore: async (event) => {
		requireUser(event, ['superadmin']);
		const form = await event.request.formData();

		try {
			await backend(event, `/superadmin/admins/${form.get('user_id')}/restore`, { method: 'PATCH' });
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to reactivate admin.') });
		}

		return { success: 'Admin reactivated.' };
	}
};
