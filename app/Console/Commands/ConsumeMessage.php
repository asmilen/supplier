<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class ConsumeMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message:consume {exchange} {key*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume messages via CloudAMQP';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
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

        $channel->exchange_declare($this->argument('exchange'), 'topic', false, true, false);

        list($queueName, ,) = $channel->queue_declare('', false, false, true, false);

        foreach($this->argument('key') as $bindingKey) {
            $channel->queue_bind($queueName, $this->argument('exchange'), $bindingKey);
        }

        $callback = function ($message) {
            $this->processMessage($message);
        };

        $channel->basic_consume($queueName, '', false, true, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();

        $connection->close();
    }

    protected function processMessage($message)
    {
        \Log::info(' [x] '.$message->delivery_info['routing_key'].':'.$message->body."\n");
    }
}
