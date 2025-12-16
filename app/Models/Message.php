<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'message',
        'processed_message',
        'is_processed',
    ];

    protected $casts = [
        'is_processed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
