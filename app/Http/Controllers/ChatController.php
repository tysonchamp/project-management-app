<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $users = User::where('id', '!=', Auth::id())->get();
        return view('chat.index', compact('users'));
    }

    public function fetchMessages(Request $request)
    {
        $query = ChatMessage::with('sender');

        if ($request->is_group) {
            $query->where('is_group', true);
        } else {
            $otherUserId = $request->user_id;
            $query->where(function ($q) use ($otherUserId) {
                $q->where('sender_id', Auth::id())->where('receiver_id', $otherUserId);
            })->orWhere(function ($q) use ($otherUserId) {
                $q->where('sender_id', $otherUserId)->where('receiver_id', Auth::id());
            });
        }

        return response()->json($query->oldest()->get());
    }

    public function sendMessage(Request $request)
    {
        $data = [
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'is_group' => $request->is_group ?? false,
        ];

        if (!$data['is_group']) {
            $data['receiver_id'] = $request->receiver_id;
        }

        $message = ChatMessage::create($data);

        return response()->json($message->load('sender'));
    }
}
