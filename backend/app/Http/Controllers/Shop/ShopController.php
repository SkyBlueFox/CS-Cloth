<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * รายการสินค้าทั้งหมดที่เปิดใช้งาน
     */
    public function index()
    {
        $items = Item::query()
            ->where('is_active', true)
            ->latest()
            ->paginate(12);

        return response()->json($items);
    }

    /**
     * รายละเอียดสินค้ารายชิ้น พร้อมคำถามและคำตอบ
     */
    public function show(Item $item, Request $request)
    {
        // ตรวจสอบว่าสินค้ายังเปิดใช้งานอยู่ (ถ้าใช้ SoftDeletes จะกรองออกให้อัตโนมัติ)
        if (!$item->is_active) {
            return response()->json(['message' => 'Item is not available'], 404);
        }

        // โหลดคำถามพร้อมข้อมูลผู้ถาม
        $item->load(['questions' => function ($query) {
            $query->latest();
        }]);

        // ดึง User ปัจจุบัน (ถ้ามี) จาก token
        $user = auth('sanctum')->user() ?? $request->user();

        // ปรับแต่งข้อมูลคำถามเพื่อเพิ่มสถานะ report สำหรับ UI
        $questions = $item->questions->map(function ($question) use ($user) {
            $data = $question->toArray();
            
            // เช็กว่า User ปัจจุบันเคยกด Report คำถามนี้หรือยัง
            $data['is_reported_by_current_user'] = $user 
                ? $question->reports()->where('user_id', $user->id)->exists() 
                : false;
                
            return $data;
        });

        // ส่งข้อมูลทั้งหมดกลับไปให้ Frontend
        return response()->json([
            'item' => $item,
            'questions' => $questions
        ]);
    }
}