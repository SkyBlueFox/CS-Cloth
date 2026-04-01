import { redirect } from '@sveltejs/kit';

export const load = ({ url }) => {
	const query = url.searchParams.toString();
	throw redirect(308, query ? `/items?${query}` : '/items');
};
