<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\ChatHistory;

class ChatBotController extends Controller
{
    public function chat(Request $request) {
        $request->validate([
            'message' => 'required|string'
        ]);

        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $sessionId = $request->session_id ?? Str::uuid()->toString(); 

        $previousMessages = ChatHistory::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($chat) => [
                ['role' => 'user', 'content' => $chat->user_message],
                ['role' => 'assistant', 'content' => $chat->bot_response],
            ])
            ->flatten(1)
            ->toArray();

        $messages = array_merge($previousMessages, [
            ['role' => 'user', 'content' => $request->message]
        ]);

        $response = Http::timeout(120)->post('http://localhost:11434/api/chat', [
            'model' => 'mistral',
            'messages' => $messages,
            'stream' => false,
        ]);

        $responseData = $response->json();
    
        $botResponse = $responseData['message']['content'] ?? 'No response from the AI bot';

        ChatHistory::create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'user_message' => $request->message,
            'bot_response' => $botResponse,
        ]);

        return response()->json([
            'session_id' => $sessionId,
            'response' => $botResponse
        ]);
    }
}
