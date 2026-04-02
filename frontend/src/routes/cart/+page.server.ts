import { backend, getErrorMessage } from '$lib/server/backend';
import { fail, redirect } from '@sveltejs/kit';
import type { CartItem } from '$lib/types';
import type { PageServerLoad, Actions } from './$types';

export const load: PageServerLoad = async (event) => {
    if (!event.locals.user) {
        throw redirect(302, '/login');
    }

    try {
        const response = await backend<{ data: CartItem[] }>(event, '/cart');
        return { 
            cart: response?.data ?? [] 
        };
    } catch (err) {
        console.error('Cart load error:', err);
        return {
            cart: [],
            error: getErrorMessage(err, 'ไม่สามารถโหลดข้อมูลตะกร้าสินค้าได้')
        };
    }
};

export const actions: Actions = {
    remove: async (event) => {
        const form = await event.request.formData();
        const itemId = form.get('item_id');

        try {
            await backend(event, `/cart/${itemId}`, { method: 'DELETE' });
            return { success: 'ลบสินค้าออกจากตะกร้าแล้ว' };
        } catch (err) {
            return fail(422, { error: getErrorMessage(err, 'ไม่สามารถลบสินค้าได้') });
        }
    },
    update: async (event) => {
        const form = await event.request.formData();
        const itemId = form.get('item_id');
        const quantity = Number(form.get('quantity') ?? 1);

        try {
            await backend(event, `/cart/${itemId}`, {
                method: 'PUT',
                body: { quantity }
            });
            return { success: 'อัปเดตจำนวนสินค้าเรียบร้อย' };
        } catch (err) {
            return fail(422, { error: getErrorMessage(err, 'ไม่สามารถอัปเดตจำนวนได้') });
        }
    }
};