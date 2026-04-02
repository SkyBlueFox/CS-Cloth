import { redirect } from '@sveltejs/kit';
import { landingFor } from '$lib/server/auth';

export const load = async ({ locals }) => {
	redirect(303, landingFor(locals.user));
};
