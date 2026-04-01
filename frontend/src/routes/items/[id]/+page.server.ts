import { fail, error, redirect } from '@sveltejs/kit';
import { backend, getErrorMessage, isApiError } from '$lib/server/backend';
import type { Address, Item, Question } from '$lib/types';
import type { Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async (event) => {
    try {
        const response = await backend<{ item: Item; questions: Question[] }>(event, `/items/${event.params.id}`);

        let addressesData: Address[] = [];

        if (event.locals.user?.role === 'user') {
            try {
                const addressesRes = await backend<{ data: Address[] }>(event, '/addresses');
                addressesData = addressesRes.data || [];
            } catch (addrErr) {
                console.error('Error loading addresses:', addrErr);
            }
        }

        return {
            ...response,
            addresses: addressesData,
            viewerRole: event.locals.user?.role ?? null
        };
    } catch (err) {
        console.error('Error loading item details:', err);
        if (isApiError(err)) {
            throw error(err.status, 'ไม่สามารถโหลดข้อมูลสินค้าได้');
        }
        throw error(500, 'Internal Server Error');
    }
};

export const actions: Actions = {
    // เพิ่มสินค้าลงตะกร้า
    addToCart: async (event) => {
        const user = event.locals.user;
        if (!user || user.role !== 'user') {
            return fail(403, { error: 'กรุณาเข้าสู่ระบบด้วยบัญชีผู้ใช้ปกติ' });
        }

        const form = await event.request.formData();
        const quantity = Number(form.get('quantity') ?? 1);

        try {
            await backend(event, '/cart', {
                method: 'POST',
                body: {
                    item_id: Number(event.params.id),
                    quantity: quantity
                }
            });
            return { success: 'เพิ่มสินค้าลงตะกร้าเรียบร้อยแล้ว' };
        } catch (err) {
            return fail(422, { error: getErrorMessage(err, 'ไม่สามารถเพิ่มสินค้าลงตะกร้าได้') });
        }
    },

    question: async (event) => {
        const user = event.locals.user;
        if (!user || user.role !== 'user') return fail(403, { error: 'Forbidden' });
        const form = await event.request.formData();
        try {
            await backend(event, `/items/${event.params.id}/questions`, {
                method: 'POST',
                body: { question_text: String(form.get('question_text') ?? '') }
            });
            return { success: 'Question submitted.' };
        } catch (err) {
            return fail(422, { error: getErrorMessage(err, 'Unable to submit question.') });
        }
    },

    report: async (event) => {
        const user = event.locals.user;
        if (!user || user.role !== 'user') return fail(403, { error: 'Forbidden' });
        const form = await event.request.formData();
        try {
            await backend(event, `/questions/${form.get('question_id')}/report`, {
                method: 'POST',
                body: { reason: String(form.get('reason') ?? '') }
            });
            return { success: 'Report submitted.' };
        } catch (err) {
            return fail(422, { error: getErrorMessage(err, 'Unable to submit report.') });
        }
    },

    order: async (event) => {
        const user = event.locals.user;
        if (!user || user.role !== 'user') return fail(403, { error: 'Forbidden' });
        const form = await event.request.formData();
        try {
            const addressId = form.get('address_id');
            await backend(event, `/orders`, {
                method: 'POST',
                body: {
                    item_id: Number(event.params.id),
                    quantity: Number(form.get('quantity') ?? 1),
                    address_id: addressId ? Number(addressId) : null,
                    delivery_method: String(form.get('delivery_method') ?? ''),
                    save_address: form.get('save_address') === '1',
                    set_as_default: form.get('set_as_default') === '1',
                    shipping_address: addressId ? undefined : {
                        label: String(form.get('label') ?? ''),
                        recipient_name: String(form.get('recipient_name') ?? ''),
                        phone: String(form.get('phone') ?? ''),
                        line_1: String(form.get('line_1') ?? ''),
                        line_2: String(form.get('line_2') ?? ''),
                        district: String(form.get('district') ?? ''),
                        province: String(form.get('province') ?? ''),
                        postal_code: String(form.get('postal_code') ?? ''),
                        country: String(form.get('country') ?? 'Thailand')
                    }
                }
            });
        } catch (err) {
            return fail(422, { error: getErrorMessage(err, 'Unable to place order.') });
        }
        throw redirect(303, '/orders');
    }
};