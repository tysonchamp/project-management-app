<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Services\OneSignalService;

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

        // Pagination: Load older messages
        if ($request->before_id) {
            $query->where('id', '<', $request->before_id);
        }

        // Polling: Load newer messages
        if ($request->after_id) {
            $query->where('id', '>', $request->after_id);
        }

        // Default limit 50, fetch latest first
        $messages = $query->latest()->take(50)->get();

        // Return in chronological order
        return response()->json($messages->reverse()->values());
    }

    public function sendMessage(Request $request, OneSignalService $oneSignal)
    {
        $request->validate([
            'message' => 'nullable|string',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5048',
        ]);

        // Ensure at least one is present
        if (!$request->message && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Message or attachment required'], 422);
        }

        $data = [
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'is_group' => $request->is_group ?? false,
        ];

        if ($request->hasFile('attachment')) {
            // $fileName = time() . '_' . $request->file('attachment')->getClientOriginalName();
            // $request->file('attachment')->move(public_path('chat_attachments'), $fileName);
            // $data['attachment'] = 'chat_attachments/' . $fileName;
            $path = $request->file('attachment')->storeAs('chat_attachments', $request->file('attachment')->hashName(), [
                'disk' => config('filesystems.default'),
                'visibility' => 'public',
            ]);
            $data['attachment'] = $path;
        }

        if (!$data['is_group']) {
            $data['receiver_id'] = $request->receiver_id;
        }

        $message = ChatMessage::create($data);

        // Send OneSignal Notification
        $sender = Auth::user();
        
        $msgContent = $sender->name . ": ";
        if ($message->message) {
            $msgContent .= $message->message;
        } elseif ($message->attachment) {
            $msgContent .= "Sent an image 📷";
        }

        if ($data['is_group']) {
            $userIds = User::where('id', '!=', $sender->id)->pluck('id')->toArray();
            $title = "New Group Message";
            $msg = $msgContent;
        } else {
            $userIds = [$request->receiver_id];
            $title = "New Message from " . $sender->name;
            $msg = $sender->name . " sent you a message";
            if ($message->attachment && !$message->message) {
                 $msg = $sender->name . " sent you an image 📷";
            } elseif ($message->message) {
                 $msg = $message->message;
            }
        }

        $oneSignal->sendNotification(
            $userIds,
            $title,
            $msg,
            route('chat.index')
        );

        return response()->json($message->load('sender'));
    }
}
