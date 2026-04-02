<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $questions = Question::with('item')
            ->where('asker_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $questions
        ]);
    }

    public function store(Request $request, Item $item)
    {
        $validated = $request->validate([
            'question_text' => 'required|string|max:255',
        ]);

        $question = Question::create([
            'item_id' => $item->id,
            'asker_id' => Auth::id(),
            'asker_name' => Auth::user()->name,
            'question_text' => $validated['question_text'],
            'score_cached' => 0,
        ]);

        return response()->json([
            'message' => 'Question submitted',
            'data' => $question
        ], 201);
    }
}