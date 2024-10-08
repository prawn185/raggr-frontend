<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function index()
    {

        try {
            $startTime = microtime(true);
            $response = Http::timeout(5)->get(config('services.llm_api.url') . 'health');
            $llmApiStatus = $response->successful();
            $endTime = microtime(true);
        } catch (\Exception $e) {
            Log::error('Error checking LLM API status: ' . $e->getMessage());
        }

        return Inertia::render('Chat', [
            'llm_api_status' => $llmApiStatus ?? false,
        ]);
    }

    public function retrieveChats()
    {
        $chats = Chat::where('user_id', auth()->id())->get();
        return response()->json($chats);
    }

    public function show(Chat $chat)
    {
        return Inertia::render('Pages/Chat', compact('chat'));
    }

    public function create()
    {
        return Inertia::render('Pages/CreateChat');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
        ]);
    
        $chat = Chat::create([
            'id' => Str::uuid(),
            'title' => $validatedData['title'],
            'user_id' => auth()->id(),
        ]);
    
        return response()->json($chat);
    }

    public function update(Request $request, Chat $chat)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $chat->update($validatedData);

        return response()->json($chat);
    }

    public function destroy(Chat $chat)
    {
        $chat->delete();

        return response()->json(['message' => 'Chat soft deleted successfully']);
    }

    public function forceDestroy(Chat $chat)
    {
        $chat->forceDelete();

        return response()->json(['message' => 'Chat permanently deleted successfully']);
    }

    public function restore($id)
    {
        $chat = Chat::withTrashed()->findOrFail($id);
        $chat->restore();

        return response()->json(['message' => 'Chat restored successfully']);
    }
}