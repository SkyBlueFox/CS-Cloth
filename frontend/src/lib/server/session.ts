import type { Cookies } from '@sveltejs/kit';

export const AUTH_COOKIE = 'cscloth_token';

const cookieOptions = {
	path: '/',
	httpOnly: true,
	sameSite: 'lax' as const,
	secure: false
};

export function getAuthToken(cookies: Cookies): string | null {
	return cookies.get(AUTH_COOKIE) ?? null;
}

export function setAuthToken(cookies: Cookies, token: string) {
	cookies.set(AUTH_COOKIE, token, cookieOptions);
}

export function clearAuthToken(cookies: Cookies) {
	cookies.delete(AUTH_COOKIE, cookieOptions);
}
