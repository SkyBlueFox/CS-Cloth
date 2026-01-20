<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $onlyUnanswered = $request->boolean('unanswered');

        $query = Question::query()
            ->with(['item', 'asker', 'admin'])
            ->latest();

        if ($onlyUnanswered) {
            $query->whereNull('answer_text');
        }

        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('question_text', 'like', "%{$search}%")
                  ->orWhere('answer_text', 'like', "%{$search}%");
            });
        }

        $questions = $query->paginate(15)->withQueryString();

        return view('admin.questions', compact('questions'));
    }

    public function answer(Request $request, Question $question)
    {
        $request->validate([
            'answer_text' => ['required', 'string', 'max:2000'],
        ]);

        $me = $request->user();

        if ($question->admin_id === null) {
            $question->admin_id = $me->id;
            $question->admin_name = $me->name;
        }

        abort_unless($question->admin_id === $me->id, 403);

        $question->answer_text = $request->input('answer_text');
        $question->save();

        return back()->with('success', 'Answer saved.');
    }

    public function deleteAnswer(Request $request, Question $question)
    {
        $me = $request->user();
        abort_unless($question->admin_id === $me->id, 403);

        $question->update([
            'answer_text'  => null,
            'admin_id'     => null,
            'admin_name'   => null,
            'score_cached' => 0,
        ]);

        return back()->with('success', 'Answer removed.');
    }
}
