<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Question;
use App\Models\Report;
use App\Support\ApiData;
use Illuminate\Http\Request;

class UserQuestionController extends Controller
{
    public function index(Request $request)
    {
        $questions = Question::query()
            ->with('item')
            ->where('asker_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        $latestReportsByQuestion = Report::query()
            ->where('reporter_id', $request->user()->id)
            ->whereIn('question_id', collect($questions->items())->pluck('id'))
            ->latest('created_at')
            ->get()
            ->unique('question_id')
            ->keyBy('question_id');

        collect($questions->items())->each(function (Question $question) use ($latestReportsByQuestion) {
            $latestReport = $latestReportsByQuestion->get($question->id);
            $question->is_reported_by_current_user = (bool) $latestReport;
            $question->current_user_report_status = $latestReport?->status;
        });

        return response()->json(ApiData::pagination($questions, fn (Question $question) => ApiData::question($question)));
    }

    public function store(Request $request, Item $item)
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string', 'max:255'],
        ]);

        $question = Question::create([
            'item_id' => $item->id,
            'asker_id' => $request->user()->id,
            'asker_name' => $request->user()->name,
            'question_text' => $validated['question_text'],
            'admin_id' => null,
            'admin_name' => null,
            'answer_text' => null,
            'score_cached' => 0,
        ]);

        return response()->json([
            'question' => ApiData::question($question),
        ], 201);
    }

    public function report(Request $request, Question $question)
    {
        if (!$question->answer_text) {
            return response()->json(['message' => 'You cannot report a question that has not been answered yet.'], 422);
        }

        $hasActiveReport = Report::query()
            ->where('question_id', $question->id)
            ->where('reporter_id', $request->user()->id)
            ->where('status', Report::STATUS_PENDING)
            ->exists();

        if ($hasActiveReport) {
            return response()->json(['message' => 'You already have a pending report for this answer.'], 422);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:255'],
        ]);

        $question->load('admin');

        $report = Report::create([
            'reporter_id' => $request->user()->id,
            'reporter_name' => $request->user()->name,
            'admin_id' => $question->admin_id,
            'admin_name' => $question->admin?->name ?? 'Unassigned',
            'question_id' => $question->id,
            'question_text_snapshot' => $question->question_text,
            'answer_text_snapshot' => $question->answer_text,
            'reason' => $validated['reason'],
            'status' => Report::STATUS_PENDING,
        ]);

        return response()->json([
            'report' => ApiData::report($report),
        ], 201);
    }
}
