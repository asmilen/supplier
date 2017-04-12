<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class PublishMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $exchange;

    protected $routingKey;

    protected $body;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($exchange, $routingKey, $body)
    {
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
        $this->body = $body;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $connection = new AMQPStreamConnection(
            config('services.amqp.host'),
            config('services.amqp.port'),
            config('services.amqp.user'),
            config('services.amqp.password'),
            config('services.amqp.vhost')
        );

        $channel = $connection->channel();

        $channel->exchange_declare($this->exchange, 'topic', false, false, false);

        $message = new AMQPMessage($this->body);

        $channel->basic_publish($message, $this->exchange, $this->routingKey);

        $channel->close();

        $connection->close();
    }
}
