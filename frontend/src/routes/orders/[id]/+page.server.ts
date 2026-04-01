import { fail, error } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';
import type { Order } from '$lib/types';
import type { PageServerLoad, Actions } from './$types';

export const load: PageServerLoad = async (event) => {
    requireUser(event, ['user']);
    try {
        const response = await backend<{ order: Order }>(event, `/orders/${event.params.id}`);
        if (!response?.order) throw error(404, 'Order not found');
        return { order: response.order };
    } catch (err) {
        console.error('Order detail load error:', err);
        throw error(500, getErrorMessage(err, 'Could not load order details'));
    }
};

export const actions: Actions = {
    cancel: async (event) => {
        requireUser(event, ['user']);
        try {
            await backend(event, `/orders/${event.params.id}/cancel`, { method: 'PATCH' });
            return { success: 'Order cancelled.' };
        } catch (err) {
            return fail(422, { error: getErrorMessage(err, 'Unable to cancel order.') });
        }
    },
    refund: async (event) => {
        requireUser(event, ['user']);
        const form = await event.request.formData();
        try {
            // สำคัญ: ส่ง FormData (ที่มีไฟล์) ไปยัง backend
            await backend(event, `/orders/${event.params.id}/refund`, {
                method: 'POST', // แนะนำใช้ POST สำหรับการส่งไฟล์
                body: form
            });
            return { success: 'Refund request submitted successfully.' };
        } catch (err) {
            return fail(422, { error: getErrorMessage(err, 'Unable to submit refund request.') });
        }
    }
};