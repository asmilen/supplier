<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageQueueLog extends Model
{
    protected $table = 'message_queue_logs';

    public $timestamps = false;

    protected $fillable = ['exchange', 'routingKey', 'body', 'created_at', 'updated_at'];
}
