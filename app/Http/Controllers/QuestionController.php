<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    /**
     * ADMIN: Pending Questions + History (answered by me)
     * route: admin.questions.index -> /admin/questions
     */
    public function index()
    {
        $pending = Question::with(['item', 'asker'])
            ->whereNull('answer_text')
            ->latest()
            ->paginate(10, ['*'], 'pending');

        $answeredByMe = Question::with(['item', 'asker'])
            ->whereNotNull('answer_text')
            ->where('admin_id', Auth::id())
            ->latest()
            ->paginate(10, ['*'], 'answered');

        return view('admin.questions', compact('pending', 'answeredByMe'));
    }

    /**
     * ADMIN: Answer question
     * route: admin.questions.answer -> PATCH /admin/questions/{question}/answer
     */
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

    /**
     * ADMIN: delete answer (optional route มีแล้วใน web.php)
     */
    public function deleteAnswer(Question $question)
    {
        if ($question->admin_id !== Auth::id()) {
            return back()->with('error', 'คุณลบคำตอบของคนอื่นไม่ได้');
        }

        $question->update([
            'answer_text' => null,
            'admin_id' => null,
            'admin_name' => null,
            'score_cached' => 0,
        ]);

        return back()->with('success', 'ลบคำตอบเรียบร้อย');
    }
}
