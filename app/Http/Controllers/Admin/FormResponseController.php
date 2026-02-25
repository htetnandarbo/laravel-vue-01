<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateFormResponseStatusRequest;
use App\Models\FormResponse;
use App\Models\Qr;
use Inertia\Inertia;
use Inertia\Response;

class FormResponseController extends Controller
{
    public function index(Qr $qr)
    {
        return redirect()->route('admin.qrs.responses', ['qr' => $qr->id]);
    }

    public function show(FormResponse $response): Response
    {
        $response->load('qr', 'answers.question');

        return Inertia::render('admin/Qrs/ResponseShow', [
            'response' => [
                'id' => $response->id,
                'status' => $response->status,
                'user_identifier' => $response->user_identifier,
                'created_at' => optional($response->created_at)->toDateTimeString(),
                'qr' => [
                    'id' => $response->qr?->id,
                    'name' => $response->qr?->name,
                    'token' => $response->qr?->token,
                ],
                'answers' => $response->answers->map(fn ($answer) => [
                    'id' => $answer->id,
                    'question' => $answer->question?->label ?? $answer->question?->question_text,
                    'value' => $answer->value,
                ])->values(),
            ],
        ]);
    }

    public function update(UpdateFormResponseStatusRequest $request, FormResponse $response)
    {
        $response->update($request->validated());

        return back();
    }
}
