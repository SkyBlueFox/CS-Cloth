<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function store(Request $request, $itemId)
    {
        $request->validate([
            'question_text' => ['required', 'string', 'max:255'],
        ]);


        $item = Item::findOrFail($itemId);

        $user = $request->user();

        Question::create([
            'item_id'      => $item->getKey(),   // ใช้จาก route param
            'asker_id'     => $user->id,
            'asker_name'   => $user->name,
            'question_text'=> $request->input('question_text'),
        ]);

        return back()->with('success', 'Question posted! Waiting for an admin to answer.');
    }
}
