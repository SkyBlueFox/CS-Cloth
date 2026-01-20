<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Item;

class ShopController extends Controller
{
    public function index()
    /*{
        $items = Item::query()
            ->where('is_active', true)
            ->latest()
            ->paginate(12);

        return view('shop.index', compact('items'));
    }*/

    /*public function show(Item $item)*/
    {
        /*abort_unless($item->is_active, 404);*/
        return view('shop.show', compact('item'));
    }
}
