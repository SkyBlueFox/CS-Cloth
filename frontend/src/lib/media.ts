import type { Item } from '$lib/types';

export function itemImageSrc(item: Pick<Item, 'image_path' | 'image_url'>): string | null {
	if (item.image_path) {
		const encodedPath = item.image_path
			.split('/')
			.map((segment) => encodeURIComponent(segment))
			.join('/');

		return `/media/${encodedPath}`;
	}

	return item.image_url;
}
