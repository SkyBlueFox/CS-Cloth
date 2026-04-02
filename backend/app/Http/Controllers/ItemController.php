<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ItemController extends Controller
{
    public function index()
    {
        $items = Cache::remember('items', 60, function () {
            return Item::where('is_active', true)->get();
        });

        return response()->json([
            'data' => $items
        ]);
    }

    public function show(Item $item)
    {
        return response()->json([
            'data' => $item
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $item = Item::create([
            'name' => $request->name,
            'created_by_id' => Auth::id(),
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image_path' => $request->image_path,
        ]);

        return response()->json([
            'message' => 'Item created',
            'data' => $item
        ], 201);
    }

    public function update(Request $request, Item $item)
    {
        $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'numeric|min:0',
            'stock' => 'integer|min:0',
        ]);

        $item->update($request->all());

        return response()->json([
            'message' => 'Item updated',
            'data' => $item
        ]);
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return response()->json([
            'message' => 'Item deleted'
        ]);
    }
}