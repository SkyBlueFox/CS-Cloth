<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Question $question)
    {
        if (!$question->answer_text) {
            return back()->with('error', 'You cannot report a question that has not been answered yet.');
        }

        return view('reports.create', compact('question'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Question $question)
    {
        $question->load('admin');

        $hasActiveReport = Report::where('question_id', $question->id)
            ->where('reporter_id', Auth::id())
            ->where('status', 'pending')
            ->exists();

        if ($hasActiveReport) {
            return redirect()->route('questions.index')->with('error', 'You already have a report pending for this answer.');
        }

        $request->validate([
            'reason' => 'required|string|min:10|max:255',
        ]);

        Report::create([
            'reporter_id' => Auth::id(),
            'reporter_name' => Auth::user()->name,

            'admin_id'    => $question->admin_id,
            'admin_name' => $question->admin ? $question->admin->name : 'Unassigned',
            'question_id' => $question->id,

            'question_text_snapshot' => $question->question_text,
            'answer_text_snapshot'   => $question->answer_text,

            'reason' => $request->reason,
            'status' => Report::STATUS_PENDING,
        ]);

        return redirect()->route('questions.index')->with('success', 'Report submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        //
    }

}
