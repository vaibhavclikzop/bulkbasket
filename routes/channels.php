<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{customerId}', function ($user, $customerId) {
    // You can replace this with your real logic
    // e.g. return $user->id === (int) $customerId;
    return true;
});
