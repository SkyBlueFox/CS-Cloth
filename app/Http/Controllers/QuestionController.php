<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    // ADMIN: pending questions
    public function index()
    {
        $questions = Question::with(['item', 'asker'])
            ->whereNull('answer_text')
            ->latest()
            ->paginate(10);

        return view('admin.questions', compact('questions'));
    }

    // ADMIN: answer
    public function answer(Request $request, Question $question)
    {
        $validated = $request->validate([
            'answer_text' => 'required|string|max:5000',
        ]);

        $question->update([
            'answer_text' => $validated['answer_text'],
            'admin_id' => Auth::id(),
            'admin_name' => Auth::user()->name,
        ]);

        return back()->with('success', 'ตอบคำถามเรียบร้อย');
    }
}
