import { env } from '$env/dynamic/private';
import type { RequestEvent } from '@sveltejs/kit';

// 🔥 แก้ default ให้ตรงกับ Laravel Sail (port 80)
const backendBase = env.BACKEND_URL ?? 'http://laravel-api';

export class ApiError extends Error {
	status: number;
	data: unknown;

	constructor(status: number, data: unknown) {
		super(
			typeof data === 'object' && data && 'message' in data
				// eslint-disable-next-line @typescript-eslint/no-explicit-any
				? String((data as any).message)
				: `API error ${status}`
		);
		this.status = status;
		this.data = data;
	}
}

export function isApiError(error: unknown): error is ApiError {
	return error instanceof ApiError;
}

export async function backend<T>(
	event: Pick<RequestEvent, 'fetch' | 'locals'>,
	path: string,
	options: {
		method?: string;
		body?: FormData | Record<string, unknown>;
		headers?: HeadersInit;
		auth?: boolean;
	} = {}
): Promise<T> {
	const headers = new Headers(options.headers);
	headers.set('accept', 'application/json');

	let body: BodyInit | undefined;

	if (options.body instanceof FormData) {
		body = options.body;
	} else if (options.body) {
		headers.set('content-type', 'application/json');
		body = JSON.stringify(options.body);
	}

	// 🔐 แนบ token ถ้ามี
	if (options.auth !== false && event.locals.authToken) {
		headers.set('authorization', `Bearer ${event.locals.authToken}`);
	}

	// 🔥 ต่อ /api prefix
	const url = `${backendBase}/api${path}`;

	const response = await event.fetch(url, {
		method: options.method ?? (body ? 'POST' : 'GET'),
		headers,
		body
	});

	const contentType = response.headers.get('content-type') ?? '';

	const data = contentType.includes('application/json')
		? await response.json()
		: await response.text();

	if (!response.ok) {
		throw new ApiError(response.status, data);
	}

	return data as T;
}

// helper ดึง error message
export function getErrorMessage(error: unknown, fallback = 'Something went wrong.') {
	if (isApiError(error)) {
		if (typeof error.data === 'object' && error.data && 'errors' in error.data) {
			const firstEntry = Object.values(
				// eslint-disable-next-line @typescript-eslint/no-explicit-any
				(error.data as any).errors as Record<string, string[]>
			)[0];

			if (Array.isArray(firstEntry) && firstEntry.length > 0) {
				return firstEntry[0];
			}
		}

		if (typeof error.data === 'object' && error.data && 'message' in error.data) {
			// eslint-disable-next-line @typescript-eslint/no-explicit-any
			return String((error.data as any).message);
		}
	}

	return fallback;
}