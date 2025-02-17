<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatHistory extends Model
{
    protected $table = 'chat_histories';

    protected $fillable = ['user_id', 'session_id', 'user_message', 'bot_response'];
}

