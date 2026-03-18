<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageSent implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(ChatMessage $message)
    {
        $this->message = $message;

        // Log when event is instantiated
        Log::info('🚀 MessageSent Event Constructed', [
            'customer_id' => $message->customer_id,
            'message_id' => $message->id ?? null,
            'sender_type' => $message->sender_type ?? null,
        ]);
    }

    public function broadcastOn()
    {
        $channel = new Channel('chat.' . $this->message->customer_id);

        // Log which channel it will broadcast to
        Log::info('📡 Broadcasting on channel', [
            'channel' => 'chat.' . $this->message->customer_id,
        ]);

        return $channel;
    }

    public function broadcastWith()
    {
        // Log the actual payload being sent
        Log::info('📤 Broadcast Payload', [
            'message' => $this->message,
        ]);

        return [
            'message' => $this->message,
        ];
    }
}
