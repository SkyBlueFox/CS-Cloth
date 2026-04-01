import { fail } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';
import type { Order, Paginated } from '$lib/types';
import type { PageServerLoad, Actions } from './$types';

export const load: PageServerLoad = async (event) => {
    requireUser(event, ['user']);
    
    const page = Number(event.url.searchParams.get('page') ?? '1');
    const search = event.url.searchParams.get('search')?.trim() ?? '';
    const params = new URLSearchParams({ page: String(page) });

    if (search) params.set('search', search);

    try {
        const orders = await backend<Paginated<Order>>(event, `/orders?${params.toString()}`);
        return {
            orders: orders || { data: [], meta: { total: 0, current_page: 1, last_page: 1, per_page: 1 } },
            filters: { search }
        };
    } catch (err) {
        console.error('Orders Load Error:', err);
        return {
            orders: { data: [], meta: { total: 0, current_page: 1, last_page: 1, per_page: 1 } },
            filters: { search },
            error: getErrorMessage(err, 'ไม่สามารถโหลดประวัติการสั่งซื้อได้')
        };
    }
};

export const actions: Actions = {
    cancel: async (event) => {
        requireUser(event, ['user']);
        const form = await event.request.formData();
        try {
            await backend(event, `/orders/${form.get('order_id')}/cancel`, { method: 'PATCH' });
            return { success: 'Order cancelled successfully.' };
        } catch (error) {
            return fail(422, { error: getErrorMessage(error, 'Unable to cancel order.') });
        }
    },
    refund: async (event) => {
        requireUser(event, ['user']);
        const form = await event.request.formData();
        try {
            await backend(event, `/orders/${form.get('order_id')}/refund`, { method: 'PATCH' });
            return { success: 'Refund request submitted.' };
        } catch (error) {
            return fail(422, { error: getErrorMessage(error, 'Unable to request refund.') });
        }
    }
};