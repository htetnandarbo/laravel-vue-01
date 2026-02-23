<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $questions = Question::get();
        return Inertia::render('questions/Index', compact('questions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $question = [
            'question_text' => '',
            'type' => '',
            'is_required' => false,
            'order' => 0,
            'options' => [],
        ];
        return Inertia::render('questions/CreateEdit', compact('question'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|in:text,textarea,radio,checkbox,select,number,date',
            'is_required' => 'nullable|boolean',
            'order' => 'nullable|integer',
            'options' => 'nullable|array',
            'options.*.option_text' => 'required_if:type,radio,checkbox,select|string'
        ]);

        DB::beginTransaction();

        try {

            // Create question
            $question = Question::create([
                'question_text' => $validated['question_text'],
                'type' => $validated['type'],
                'is_required' => $validated['is_required'] ?? false,
                'order' => $validated['order'] ?? 0,
            ]);

            // If type needs options
            if (in_array($validated['type'], ['radio', 'checkbox', 'select']) && isset($validated['options'])) {

                foreach ($validated['options'] as $index => $option) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $option['option_text'],
                        'order' => $index,
                    ]);
                }
            }

            DB::commit();

            return Inertia::render('questions/Index');
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
