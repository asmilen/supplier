<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogMessageQueue extends Model
{

    protected $table = 'log_message_queue';
    protected $fillable = [
        'post_data',
        'response',
        'created_at',
        'updated_at'
    ];
}
