import { backend, getErrorMessage } from '$lib/server/backend';
import { fail, redirect } from '@sveltejs/kit';
import type { Address, CartItem, Item } from '$lib/types';
import type { Actions, PageServerLoad } from './$types';

interface CheckoutItem {
    item_id: number;
    name: string;
    price: number;
    quantity: number;
    image_path: string | null;
}

export const load: PageServerLoad = async (event) => {
    if (!event.locals.user) throw redirect(302, '/login');

    const itemId = event.url.searchParams.get('item_id');
    const quantity = Number(event.url.searchParams.get('quantity') || 1);

    let checkoutItems: CheckoutItem[] = [];

    try {
        if (itemId) {
            // กรณี "ซื้อเลย"
            const res = await backend<{ item: Item }>(event, `/items/${itemId}`);
            checkoutItems = [{
                item_id: res.item.id,
                name: res.item.name,
                price: Number(res.item.price), // มั่นใจว่าเป็น Number
                quantity: quantity,
                image_path: res.item.image_path // สำหรับฟังก์ชัน itemImageSrc
            }];
        } else {
            // กรณี "Checkout จากตะกร้า"
            const res = await backend<{ data: CartItem[] }>(event, '/cart');
            checkoutItems = (res.data || []).map((item: CartItem) => ({
                item_id: item.id,
                name: item.name,
                price: Number(item.price),
                quantity: item.quantity,
                image_path: item.image_path
            }));
        }

        const addrResp = await backend<{ data: Address[] }>(event, '/addresses');

        return {
            checkoutItems,
            addresses: addrResp.data || []
        };
    } catch (err) {
        console.error('Checkout Load Error:', err);
        return { checkoutItems: [], addresses: [] };
    }
};

export const actions: Actions = {
    placeOrder: async (event) => {
        const form = await event.request.formData();
        
        // ดึงข้อมูล JSON string ของ items ออกมา
        const itemsJson = form.get('items_json');
        if (!itemsJson) return fail(400, { error: 'No items in checkout' });

        const items = JSON.parse(itemsJson as string);
        const addressId = form.get('address_id');
        const deliveryMethod = form.get('delivery_method');

        try {
            await backend(event, '/orders', {
                method: 'POST',
                body: {
                    items: items, // ส่ง Array [{item_id, quantity}, ...]
                    address_id: addressId ? Number(addressId) : null,
                    delivery_method: deliveryMethod,
                    // ข้อมูลสำหรับที่อยู่ใหม่ (กรณีไม่ได้เลือกที่อยู่เดิม)
                    shipping_address: addressId ? undefined : {
                        label: form.get('label'),
                        recipient_name: form.get('recipient_name'),
                        phone: form.get('phone'),
                        line_1: form.get('line_1'),
                        district: form.get('district'),
                        province: form.get('province'),
                        postal_code: form.get('postal_code'),
                        country: 'Thailand'
                    },
                    save_address: form.get('save_address') === '1'
                }
            });
            
            // สั่งซื้อสำเร็จ เด้งไปหน้า Order
            throw redirect(303, '/orders');
        } catch (err) {
            // เช็คว่าเป็น redirect หรือไม่ (SvelteKit ใช้ Error ในการจัดการ redirect)
            if (err instanceof Response || (err && typeof err === 'object' && 'status' in err && err.status === 303)) {
                throw err;
            }
            return fail(422, { error: getErrorMessage(err, 'Unable to place order') });
        }
    }
};