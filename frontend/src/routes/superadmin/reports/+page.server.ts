import { fail } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';
import type { Paginated, Report } from '$lib/types';

export const load = async (event) => {
	requireUser(event, ['superadmin']);
	const page = Number(event.url.searchParams.get('page') ?? '1');
	const reports = await backend<Paginated<Report>>(event, `/superadmin/reports?page=${page}`);

	return { reports };
};

export const actions = {
	resolve: async (event) => {
		requireUser(event, ['superadmin']);
		const form = await event.request.formData();

		try {
			await backend(event, `/superadmin/reports/${form.get('report_id')}/resolve`, { method: 'PATCH' });
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to resolve report.') });
		}

		return { success: 'Report resolved.' };
	},
	dismiss: async (event) => {
		requireUser(event, ['superadmin']);
		const form = await event.request.formData();

		try {
			await backend(event, `/superadmin/reports/${form.get('report_id')}/dismiss`, { method: 'PATCH' });
		} catch (error) {
			return fail(422, { error: getErrorMessage(error, 'Unable to dismiss report.') });
		}

		return { success: 'Report dismissed.' };
	}
};
