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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $questionId)
    {
        $question = Question::with('answer.user')->findOrFail($questionId);

        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        Report::create([
            'reporter_id' => Auth::id(),
            'reporter_name' => Auth::user()->name,

            'admin_id'    => $question->admin_id,
            'admin_name' => $question->admin ? $question->admin->name : 'Unassigned',
            'question_id' => $question->id,

            'question_text_snapshot' => $question->content,
            'answer_text_snapshot'   => $question->answer->content,

            'reason' => $request->reason,
            'status' => Report::STATUS_PENDING,
        ]);

        return back()->with('success', 'Report submitted successfully.');
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
