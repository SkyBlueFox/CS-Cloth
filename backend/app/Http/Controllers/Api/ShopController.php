<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Question;
use App\Support\ApiData;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::query()
            ->where('is_active', true)
            ->latest()
            ->paginate(12);

        return response()->json(ApiData::pagination($items, fn (Item $item) => ApiData::item($item)));
    }

    public function show(Request $request, Item $item)
    {
        abort_unless($item->is_active, 404);

        $item->load(['questions' => fn ($query) => $query->latest()]);

        if ($request->user()) {
            $reportedQuestionIds = $request->user()
                ->reports()
                ->whereIn('question_id', $item->questions->pluck('id'))
                ->where('status', 'pending')
                ->pluck('question_id')
                ->all();

            $item->questions->each(function (Question $question) use ($reportedQuestionIds) {
                $question->is_reported_by_current_user = in_array($question->id, $reportedQuestionIds, true);
            });
        }

        return response()->json([
            'item' => ApiData::item($item),
            'questions' => $item->questions->map(fn (Question $question) => ApiData::question($question))->values()->all(),
        ]);
    }
}
