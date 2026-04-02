import type { Item } from '$lib/types';

export function storagePathSrc(path: string | null | undefined): string | null {
	if (!path) {
		return null;
	}

	const encodedPath = path
		.split('/')
		.map((segment) => encodeURIComponent(segment))
		.join('/');

	return `/media/${encodedPath}`;
}

export function itemImageSrc(item: Pick<Item, 'image_path' | 'image_url'>): string | null {
	if (item.image_path) {
		return storagePathSrc(item.image_path);
	}

	return item.image_url;
}
