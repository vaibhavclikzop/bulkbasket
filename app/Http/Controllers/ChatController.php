<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMessage;
use App\Events\MessageSent;

class ChatController extends Controller
{
    public function getMessages($customerId)
    {
        $messages = ChatMessage::where('customer_id', $customerId)
            ->orderBy('created_at')
            ->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required',
            'message' => 'required',
        ]);
        
        $chat = ChatMessage::create([
            'customer_id' => $data['customer_id'],
            'supplier_id' => $request->user['supplier_id'],
            'sender_type' => 'supplier',
            'message' => $data['message'],
        ]);

        broadcast(new MessageSent($chat))->toOthers();

        return response()->json($chat);
    }
}
