<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $primaryKey = "id";
    protected $table = "help_support";
    protected $fillable = [
        'sender_type',
        'customer_id',
        'supplier_id',
        'message',
        'is_seen',
    ];
}
