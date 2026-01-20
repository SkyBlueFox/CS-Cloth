<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->user()->role ?? null;
        abort_unless(in_array($role, ['admin', 'superadmin'], true), 403);

        $onlyUnanswered = $request->boolean('unanswered');

        $q = Question::query()
            ->with(['item', 'asker', 'admin'])
            ->latest();

        if ($onlyUnanswered) {
            $q->whereNull('answer_text');
        }

        $questions = $q->paginate(15)->withQueryString();

        return view('admin.questions', compact('questions'));
    }

    public function answer(Request $request, Question $question)
    {
        $role = $request->user()->role ?? null;
        abort_unless(in_array($role, ['admin', 'superadmin'], true), 403);

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
        $role = $request->user()->role ?? null;
        abort_unless(in_array($role, ['admin', 'superadmin'], true), 403);

        $me = $request->user();
        abort_unless($question->admin_id === $me->id, 403);

        $question->update([
            'answer_text' => null,
            'admin_id' => null,
            'admin_name' => null,
            'score_cached' => 0,
        ]);

        return back()->with('success', 'Answer removed.');
    }
}
