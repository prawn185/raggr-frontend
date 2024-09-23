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

        $userMessage = $this->storeUserMessage($chat, $request->content);
        $chatHistory = $this->getChatHistory($chat);
        $response = $this->sendApiRequest($request->content, $chatHistory);

        if ($response->successful()) {
            $aiMessage = $this->storeAiMessage($chat, $response);
            $aiMessage->update(['latency' => $response->json('latency')]);
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

        $userMessage = $this->storeUserMessage($chat, $request->message);
        $chatHistory = $this->getChatHistory($chat);
        $response = $this->sendApiRequest($request->message, $chatHistory);

        if ($response->successful()) {
            $aiMessage = $this->storeAiMessage($chat, $response, 'response');
            $aiMessage->update(['latency' => $response->json('latency')]);
            return response()->json([
                'message' => $response->json('response'),
                'source_documents' => $response->json('source_documents'),
            ]);
        }

        return response()->json([
            'error' => 'Failed to get response from API',
        ], 500);
    }

    private function storeUserMessage(Chat $chat, string $content)
    {
        return $chat->messages()->create([
            'content' => $content,
            'is_user' => true,
        ]);
    }

    private function storeAiMessage(Chat $chat, $response, $key = 'answer')
    {
        return $chat->messages()->create([
            'content' => $response->json($key),
            'is_user' => false,
            'source_documents' => json_encode($response->json('source_documents')),
        ]);
    }

    private function getChatHistory(Chat $chat)
    {
        return $chat->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    $message->is_user ? 'Human' : 'AI' => $message->content,
                ];
            })
            ->values()
            ->toArray();
    }

    private function sendApiRequest(string $content, array $chatHistory)
    {
        return Http::withHeaders([
            'X-API-Key' => config('services.llm_api.key'),
            'Content-Type' => 'application/json',
        ])->post(config('services.llm_api.url') . 'chat', [
            'query' => $content,
            'user_id' => auth()->id(),
            'chat_history' => $chatHistory,
        ]);
    }
}
