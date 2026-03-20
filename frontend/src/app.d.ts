// See https://svelte.dev/docs/kit/types#app.d.ts

import type { User } from '$lib/types';

declare global {
	namespace App {
		interface Locals {
			authToken: string | null;
			user: User | null;
		}

		interface PageData {
			user: User | null;
		}
	}

	// ✅ FIX: เพิ่มตรงนี้
	namespace svelteHTML {
		interface HTMLAttributes {
			'sveltekit:prefetch'?: boolean;
			'sveltekit:noscroll'?: boolean;
			'sveltekit:reload'?: boolean;
		}
	}
}

export {};