import { redirect } from '@sveltejs/kit';

export const load = ({ params }) => {
	throw redirect(308, `/items/${params.id}`);
};
