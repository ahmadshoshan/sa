<?php

namespace App\Http\Controllers;

use App\Services\AssistantService;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function ask(Request $request, AssistantService $assistant)
    {
        $request->validate(['question' => 'required|string|max:500']);

        $context = $request->input('context') ?? [];

        $result = $assistant->answer($request->question, is_array($context) ? $context : []);

        return response()->json([
            'answer' => $result['answer'] ?? '',
            'context' => $result['context'] ?? null,
            'source' => $result['source'] ?? 'local',
            'suggestions' => $result['suggestions'] ?? [],
        ]);
    }
}