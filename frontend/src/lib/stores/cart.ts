import { writable } from 'svelte/store';

export type CartItem = {
  id: number;
  name: string;
  price: number;
  quantity: number;
};

export const cart = writable<CartItem[]>([]);

export function addToCart(item: Omit<CartItem, 'quantity'>, qty = 1) {
  cart.update(items => {
    const existing = items.find(i => i.id === item.id);

    if (existing) {
      existing.quantity += qty;
    } else {
      items.push({ ...item, quantity: qty });
    }

    return items;
  });
}

export function removeFromCart(id: number) {
  cart.update(items => items.filter(i => i.id !== id));
}

export function clearCart() {
  cart.set([]);
}