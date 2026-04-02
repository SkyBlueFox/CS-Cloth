import { fail, redirect, isRedirect } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { landingFor } from '$lib/server/auth';
import { setAuthToken } from '$lib/server/session';
import type { Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ locals }) => {
    if (locals.user) {
        throw redirect(303, landingFor(locals.user));
    }
};

export const actions: Actions = {
    default: async (event) => {
        const form = await event.request.formData();
        const email = String(form.get('email') ?? '');
        const password = String(form.get('password') ?? '');

        try {
            const response = await backend<{ data: { token: string } }>(event, '/auth/login', {
                method: 'POST',
                body: { email, password },
                auth: false
            });

            // บันทึก Token ลงใน Cookie
            setAuthToken(event.cookies, response.data.token);

            // Login สำเร็จเด้งไปหน้าแรก
            throw redirect(303, '/');
        } catch (error) {
            // สำคัญ: ถ้าเป็น redirect error ของ SvelteKit ให้ปล่อยผ่านเพื่อให้หน้าเปลี่ยนจริง
            if (isRedirect(error)) throw error;

            return fail(422, {
                error: getErrorMessage(error, 'Unable to login.'),
                email: email
            });
        }
    }
};