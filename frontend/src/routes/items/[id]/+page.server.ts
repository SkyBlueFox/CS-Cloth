import { fail, error, redirect } from '@sveltejs/kit';
import { backend, getErrorMessage, isApiError } from '$lib/server/backend';
import type { CartItem, Item, Question } from '$lib/types';
import type { Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async (event) => {
    try {
        // ดึงข้อมูลสินค้าและคำถาม
        const response = await backend<{ item: Item; questions: Question[] }>(event, `/items/${event.params.id}`);
        let cartQuantity = 0;

        if (event.locals.user?.role === 'user') {
            try {
                const cartResponse = await backend<{ data: CartItem[] }>(event, '/cart');
                cartQuantity = cartResponse.data.find((entry) => entry.id === Number(event.params.id))?.quantity ?? 0;
            } catch (cartError) {
                console.error('Error loading cart quantity for item detail:', cartError);
            }
        }

        return {
            ...response,
            cartQuantity,
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
    /**
     * Action: เพิ่มสินค้าลงตะกร้า (ทำงานเบื้องหลัง)
     */
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
            return fail(422, { 
                error: getErrorMessage(err, 'ไม่สามารถเพิ่มสินค้าลงตะกร้าได้') 
            });
        }
    },

    /**
     * Action: ซื้อสินค้าทันที (ส่งต่อไปหน้า Checkout)
     */
    buyNow: async (event) => {
        const user = event.locals.user;
        if (!user) {
            throw redirect(303, '/login');
        }

        const form = await event.request.formData();
        const quantity = form.get('quantity') ?? '1';

        // เด้งไปหน้า checkout โดยส่ง query parameters ไปบอกว่าต้องการซื้ออะไรและจำนวนเท่าไหร่
        throw redirect(303, `/checkout?item_id=${event.params.id}&quantity=${quantity}`);
    },

    /**
     * Action: ถามคำถาม
     */
    question: async (event) => {
        const user = event.locals.user;
        if (!user || user.role !== 'user') return fail(403, { error: 'กรุณาเข้าสู่ระบบ' });
        
        const form = await event.request.formData();
        try {
            await backend(event, `/items/${event.params.id}/questions`, {
                method: 'POST',
                body: { question_text: String(form.get('question_text') ?? '') }
            });
            return { success: 'ส่งคำถามเรียบร้อยแล้ว' };
        } catch (err) {
            return fail(422, { error: getErrorMessage(err, 'Unable to submit question.') });
        }
    },

    /**
     * Action: รายงานคำตอบ
     */
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
    }
};
