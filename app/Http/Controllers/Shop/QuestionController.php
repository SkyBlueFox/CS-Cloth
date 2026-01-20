<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function store(Request $request, Item $item)
    {
        $request->validate([
            'question_text' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();

        Question::create([
            'item_id'       => $item->id,
            'asker_id'      => $user->id,
            'asker_name'    => $user->name,
            'question_text' => $request->input('question_text'),
            'score_cached'  => 0,
        ]);

        return back()->with('success', 'Question posted! Waiting for an admin to answer.');
    }

    public function myQuestions(Request $request)
    {
        $user = $request->user();

        $questions = Question::query()
            ->with('item')
            ->where('asker_id', $user->id)
            ->latest()
            ->paginate(15);

        return view('questions.index', compact('questions'));
    }
}
