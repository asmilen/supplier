<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageQueueLog extends Model
{
    protected $table = 'message_queue_logs';
    
    protected $fillable = ['exchange', 'routingKey', 'body'];
}
