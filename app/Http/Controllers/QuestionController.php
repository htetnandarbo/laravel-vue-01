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
            'question_options' => [],
        ];
        return Inertia::render('questions/CreateEdit', compact('question'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
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

            return redirect()->route('questions.index');
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
        $question = Question::with('questionOptions')->findOrFail($id);
        return Inertia::render('questions/CreateEdit', compact('question'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
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

            $question = Question::with('questionOptions')->findOrFail($id);

            // Update question
            $question->update([
                'question_text' => $validated['question_text'],
                'type' => $validated['type'],
                'is_required' => $validated['is_required'] ?? false,
                'order' => $validated['order'] ?? 0,
            ]);

            // If question type supports options
            if (in_array($validated['type'], ['radio', 'checkbox', 'select'])) {

                $existingOptionIds = $question->questionOptions->pluck('id')->toArray();
                $incomingOptionIds = [];

                foreach ($validated['options'] ?? [] as $index => $option) {

                    // If option already exists → update
                    if (isset($option['id'])) {

                        $incomingOptionIds[] = $option['id'];

                        QuestionOption::where('id', $option['id'])
                            ->update([
                                'option_text' => $option['option_text'],
                                'order' => $index,
                            ]);
                    } else {
                        // New option → create
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_text' => $option['option_text'],
                            'order' => $index,
                        ]);
                    }
                }

                // Delete removed options
                $toDelete = array_diff($existingOptionIds, $incomingOptionIds);

                QuestionOption::whereIn('id', $toDelete)->delete();
            } else {
                // If changed to type without options → delete all options
                QuestionOption::where('question_id', $question->id)->delete();
            }

            DB::commit();

            return redirect()->route('questions.index')
                ->with('success', 'Question updated successfully');
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Question::find($id)->delete();
    }
}
