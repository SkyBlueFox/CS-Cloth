<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'string|max:5000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        Item::create([
            'name' => $request->name,
            'created_by_id' => Auth::id(),
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image_path' => $request->image_path,
        ]);

        return back()->with('success', 'Item posted!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        //
    }

    protected static function booted(): void
    {
        // Automatically save the 'updated_by' user whenever an update happens
        static::updating(function (Item $item) {
            if (Auth::check()) {
                $item->updated_by = Auth::id();
            }
        });
    }
}
