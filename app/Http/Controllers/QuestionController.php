<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{

    private function ensureAdmin(Request $request): void
    {
        $role = $request->user()->role ?? null;
        abort_unless(in_array($role, ['admin', 'superadmin'], true), 403);
    }

    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        $onlyUnanswered = $request->boolean('unanswered');
        $search = trim((string) $request->query('q', ''));

        $query = Question::query()
            ->with(['item', 'asker', 'admin'])
            ->latest();

        if ($onlyUnanswered) {
            $query->whereNull('answer_text');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('question_text', 'like', "%{$search}%")
                  ->orWhere('answer_text', 'like', "%{$search}%");
            });
        }

        $questions = $query->paginate(15)->withQueryString();

        return view('admin.questions.index', compact('questions'));
    }

    public function answer(Request $request, Question $question)
    {
        $this->ensureAdmin($request);

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
        $this->ensureAdmin($request);

        $me = $request->user();
        abort_unless($question->admin_id === $me->id, 403);

        $question->answer_text = null;
        $question->admin_id = null;
        $question->admin_name = null;
        $question->save();

        return back()->with('success', 'Answer removed.');
    }
}
