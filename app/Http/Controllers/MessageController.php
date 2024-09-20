<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index(Chat $chat)
    {
        return response()->json($chat->messages);
    }   

    public function store(Request $request, Chat $chat)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        // Store user message
        $userMessage = $chat->messages()->create([
            'content' => $request->content,
            'is_user' => true,
        ]);

        // Send request to your API
        $response = Http::withHeaders([
            'x-api-key' => config('services.llm_api.key'),
        ])->post(config('services.llm_api.url') . 'chat', [
            'query' => $request->content,
            'user_id' => auth()->id(),
        ]);

        if ($response->successful()) {
            // Store API response
            $aiMessage = $chat->messages()->create([
                'content' => $response->json('response'),
                'is_user' => false,
            ]);

            return response()->json([
                'userMessage' => $userMessage,
                'aiMessage' => $aiMessage,
            ]);
        }

        return response()->json([
            'userMessage' => $userMessage,
            'error' => 'Failed to get response from API',
        ], 500);
    }

    public function show(Chat $chat, Message $message)
    {
        return response()->json($message);
    }

    public function update(Request $request, Chat $chat, Message $message)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        // Store user message
        $chat->messages()->create([
            'content' => $request->message,
            'is_user' => true,
        ]);

        // Send request to your API
        $response = Http::post('http://127.0.0.1:8000/api/chat', [
            'query' => $request->message,
            'user_id' => auth()->id(),
        ]);

        if ($response->successful()) {
            // Store API response
            $chat->messages()->create([
                'content' => $response->json('response'),
                'is_user' => false,
            ]);

            return response()->json([
                'message' => $response->json('response'),
            ]);
        }

        return response()->json([
            'error' => 'Failed to get response from API',
        ], 500);
    }
}
