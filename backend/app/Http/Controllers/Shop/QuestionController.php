<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    /**
     * USER: My Questions
     * route: questions.index -> /questions
     */
    public function myQuestions()
    {
        $questions = Question::with('item')
            ->where('asker_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('questions.index', compact('questions'));
    }

    /**
     * USER: Ask question on item detail page
     * route: questions.store -> /shop/items/{item}/questions
     */
    public function store(Request $request, Item $item)
    {
        if (empty($request->question_text)) {
            return back()->with('info', 'Please type a question before submitting.');
        }

        $validated = $request->validate([
            'question_text' => 'required|string|max:255',
        ]);

        Question::create([
            'item_id' => $item->id,
            'asker_id' => Auth::id(),
            'asker_name' => Auth::user()->name,
            'question_text' => $validated['question_text'],

            'admin_id' => null,
            'admin_name' => null,
            'answer_text' => null,
            'score_cached' => 0,
        ]);

        return back()->with('success', 'ส่งคำถามแล้ว');
    }
}
