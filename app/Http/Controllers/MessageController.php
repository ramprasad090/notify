<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMessage;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Store a new message and dispatch it to the queue
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|integer|min:1',
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Store the message in the database
        $message = Message::create([
            'sender_id' => $request->sender_id,
            'message' => $request->message,
        ]);

        // Dispatch the message to the queue for background processing
        ProcessMessage::dispatch($message);

        return response()->json([
            'success' => true,
            'message_id' => $message->id,
            'data' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'message' => $message->message,
                'created_at' => $message->created_at,
            ],
        ], 201);
    }

    /**
     * Get all messages
     */
    public function index(): JsonResponse
    {
        $messages = Message::orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }
}
