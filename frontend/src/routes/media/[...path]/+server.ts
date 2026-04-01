import { env } from '$env/dynamic/private';
import { error } from '@sveltejs/kit';

const backendBase = env.BACKEND_URL ?? 'http://laravel-api';

export const GET = async ({ params, fetch }) => {
	const path = params.path;

	if (!path) {
		throw error(404, 'Image not found.');
	}

	const encodedPath = path
		.split('/')
		.filter(Boolean)
		.map((segment) => encodeURIComponent(segment))
		.join('/');

	const response = await fetch(`${backendBase}/storage/${encodedPath}`);

	if (!response.ok) {
		throw error(response.status, 'Image not found.');
	}

	const headers = new Headers();
	const contentType = response.headers.get('content-type');
	const cacheControl = response.headers.get('cache-control');
	const contentLength = response.headers.get('content-length');

	if (contentType) {
		headers.set('content-type', contentType);
	}

	if (cacheControl) {
		headers.set('cache-control', cacheControl);
	}

	if (contentLength) {
		headers.set('content-length', contentLength);
	}

	return new Response(response.body, {
		status: response.status,
		headers
	});
};
