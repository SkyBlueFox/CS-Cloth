<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\User;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        // ดึงรายการ Items ที่อยู่ในตะกร้าผ่านความสัมพันธ์ belongsToMany
        $items = $user->cartItems()->get();

        $data = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'price' => (float) $item->price,
                'quantity' => $item->pivot->quantity,
                'image_url' => $item->image_url,
                'image_path' => $item->image_path,
                'stock' => $item->stock,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1'
        ]);

        /** @var User $user */
        $user = $request->user();
        $item = Item::findOrFail($request->item_id);

        if ($item->stock < $request->quantity) {
            return response()->json(['message' => 'Insufficient stock'], 422);
        }

        // ตรวจสอบว่ามีสินค้านี้ในตะกร้าหรือยัง
        $existing = $user->cartItems()->where('item_id', $item->id)->first();

        if ($existing) {
            // ถ้ามีแล้ว ให้บวกจำนวนเพิ่ม
            $user->cartItems()->updateExistingPivot($item->id, [
                'quantity' => $existing->pivot->quantity + $request->quantity
            ]);
        } else {
            // ถ้ายังไม่มี ให้เชื่อมความสัมพันธ์ใหม่
            $user->cartItems()->attach($item->id, ['quantity' => $request->quantity]);
        }

        return response()->json(['message' => 'Added to cart successfully']);
    }

    public function update(Request $request, $itemId)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        /** @var User $user */
        $user = $request->user();
        $item = Item::findOrFail($itemId);

        if ($item->stock < $request->quantity) {
            return response()->json(['message' => 'Quantity exceeds stock'], 422);
        }

        // อัปเดตค่าในตาราง Pivot
        $user->cartItems()->updateExistingPivot($itemId, ['quantity' => $request->quantity]);

        return response()->json(['message' => 'Cart updated successfully']);
    }

    public function destroy(Request $request, $itemId)
    {
        /** @var User $user */
        $user = $request->user();
        // ตัดความสัมพันธ์ออกจากตาราง Pivot (ลบออกจากตะกร้า)
        $user->cartItems()->detach($itemId);

        return response()->json(['message' => 'Item removed from cart']);
    }
}