<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plans = Plan::all();
        return Inertia::render('plans/Index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $plan = new Plan();
        return Inertia::render('plans/CreateEdit', compact('plan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'display_name' => 'required',
            'detail' => 'required',
        ]);

        Plan::create($validated);
        return redirect()->route('plans.index');
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
        $plan = Plan::findOrFail($id);
        return Inertia::render('plans/CreateEdit', compact('plan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
         $validated = $request->validate([
            'name' => 'required',
            'display_name' => 'required',
            'detail' => 'required',
        ]);

        Plan::findOrFail($id)->update($validated);
        return redirect()->route('plans.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Plan::findOrFail($id)->delete();
        return redirect()->route('plans.index');
    }
}
