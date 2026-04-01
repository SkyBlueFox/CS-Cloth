import { writable } from 'svelte/store';

export type User = {
  id: number;
  name: string;
  email: string;
  role: 'user' | 'admin' | 'superadmin';
  balance: number;
  wallet_balance?: number;
};

export const user = writable<User | null>(null);
export const token = writable<string | null>(null);

export function setAuth(data: { user: User; token: string }) {
  user.set(data.user);
  token.set(data.token);

  if (typeof localStorage !== 'undefined') {
    localStorage.setItem('token', data.token);
  }
}

export function clearAuth() {
  user.set(null);
  token.set(null);

  if (typeof localStorage !== 'undefined') {
    localStorage.removeItem('token');
  }
}
