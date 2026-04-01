import { fail } from '@sveltejs/kit';
import { backend, getErrorMessage } from '$lib/server/backend';
import { requireUser } from '$lib/server/auth';
import type { Paginated, WalletTransaction } from '$lib/types';
import type { PageServerLoad, Actions } from './$types';

// กำหนด Interface ให้ชัดเจนตามที่ API ส่งมาจริง
interface WalletResponse {
    balance: number;
    transactions: Paginated<WalletTransaction>;
}

export const load: PageServerLoad = async (event) => {
    // ตรวจสอบสิทธิ์การเข้าถึง
    requireUser(event, ['user']);

    try {
        // เรียกข้อมูลจาก Backend
        // หาก backend function ของคุณมีการ throw error เมื่อ response.ok เป็น false 
        // มันจะเด้งมาที่ก้อน catch ทันที ไม่ทำให้หน้าเว็บเป็น 500
        const wallet = await backend<WalletResponse>(event, '/wallet');

        return {
            wallet: wallet || { balance: 0, transactions: { data: [], meta: { total: 0, current_page: 1, last_page: 1, per_page: 1 } } }
        };
    } catch (err) {
        console.error('Wallet Load Error:', err);
        
        // แทนที่จะปล่อยให้ 500 เราคืนค่าเริ่มต้นไปให้ UI แสดงผลแทน
        return {
            wallet: { balance: 0, transactions: { data: [], meta: { total: 0, current_page: 1, last_page: 1, per_page: 1 } } },
            error: getErrorMessage(err, 'ไม่สามารถโหลดข้อมูลกระเป๋าเงินได้')
        };
    }
};

export const actions: Actions = {
    topUp: async (event) => {
        requireUser(event, ['user']);
        const form = await event.request.formData();

        const presetAmount = form.get('preset_amount')?.toString() || '';
        const customAmount = form.get('custom_amount')?.toString() || '';
        const provider = form.get('provider')?.toString() || 'scb_easy';
        
        const cardholderName = form.get('cardholder_name')?.toString() || '';
        const cardNumber = form.get('card_number')?.toString() || '';
        const expiryDate = form.get('expiry_date')?.toString() || '';
        const cvv = form.get('cvv')?.toString() || '';

        // จัดการเรื่องจำนวนเงิน
        const rawAmount = customAmount.trim() !== '' ? customAmount : presetAmount;
        const amount = parseFloat(rawAmount);

        const values = {
            preset_amount: presetAmount,
            custom_amount: customAmount,
            provider,
            cardholder_name: cardholderName,
            card_number: cardNumber,
            expiry_date: expiryDate,
            cvv
        };

        // ตรวจสอบความถูกต้องเบื้องต้น
        if (isNaN(amount) || amount <= 0) {
            return fail(422, {
                error: 'กรุณาระบุจำนวนเงินที่ต้องการเติม',
                values
            });
        }

        try {
            const response = await backend<{ message: string }>(event, '/wallet/top-up', {
                method: 'POST',
                body: {
                    amount, // ส่งเป็น number
                    provider,
                    cardholder_name: cardholderName,
                    card_number: cardNumber,
                    expiry_date: expiryDate,
                    cvv
                }
            });

            return { 
                success: response?.message || 'ทำรายการสำเร็จ' 
            };
        } catch (err) {
            console.error('Top-up Action Error:', err);
            return fail(422, {
                error: getErrorMessage(err, 'เกิดข้อผิดพลาดในการเติมเงิน'),
                values
            });
        }
    }
};