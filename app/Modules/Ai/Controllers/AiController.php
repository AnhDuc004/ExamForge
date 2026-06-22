<?php

namespace App\Modules\Ai\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiController extends Controller
{
    use ApiResponse;

    public function generateQuestions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'min:10'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
            'difficulty' => ['nullable', 'string', 'in:easy,medium,hard'],
        ]);

        return response()->json(
            $this->successResponse('Question generation queued', [
                'job_id' => (string) Str::uuid(),
                'status' => 'queued',
                'prompt' => $validated['prompt'],
                'quantity' => $validated['quantity'] ?? 1,
                'difficulty' => $validated['difficulty'] ?? null,
            ]),
            202
        );
    }

    public function suggestFeedback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'answer_text' => ['required', 'string', 'min:3'],
            'rubric' => ['nullable', 'string'],
        ]);

        $rubric = $validated['rubric'] ?? 'Chấm theo rubric hiện có.';
        $suggestion = trim("Nhận xét nháp: câu trả lời đã nêu được ý chính. {$rubric}");

        return response()->json(
            $this->successResponse('Feedback suggestion generated', [
                'suggestion' => $suggestion,
            ])
        );
    }
}
