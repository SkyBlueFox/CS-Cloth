// See https://svelte.dev/docs/kit/types#app.d.ts
// for information about these interfaces
declare global {
	namespace App {
		// interface Error {}
		// interface Locals {}
		// interface PageData {}
		// interface PageState {}
		// interface Platform {}
	}
}

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
}

export {};
