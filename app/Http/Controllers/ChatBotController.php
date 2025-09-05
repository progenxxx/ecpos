<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatBotService;
use Illuminate\Http\JsonResponse;

class ChatBotController extends Controller
{
    protected $chatBotService;

    public function __construct(ChatBotService $chatBotService)
    {
        $this->chatBotService = $chatBotService;
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'context' => 'sometimes|array'
        ]);

        $response = $this->chatBotService->sendMessage(
            $request->input('message'),
            $request->input('context', [])
        );

        return response()->json($response);
    }

    public function getSampleQuestions(): JsonResponse
    {
        $questions = $this->chatBotService->getSampleQuestions();
        
        return response()->json([
            'success' => true,
            'questions' => $questions
        ]);
    }

    public function getWelcomeMessage(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => "👋 Hello! I'm your AI Sales Assistant. I can help you with:\n\n• Sales performance analysis\n• Business insights and trends\n• Strategic recommendations\n• Inventory optimization\n• Store performance evaluation\n\nWhat would you like to know about your business today?"
        ]);
    }
}