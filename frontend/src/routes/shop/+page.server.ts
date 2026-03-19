import { backend } from '$lib/server/backend';
import type { Item, Paginated } from '$lib/types';

export const load = async (event) => {
	const page = Number(event.url.searchParams.get('page') ?? '1');
	const items = await backend<Paginated<Item>>(event, `/shop/items?page=${page}`, { auth: false });

	return {
		items
	};
};
