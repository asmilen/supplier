<?php

namespace App\Console\Commands;

use App\Models\MessageQueueLog;
use GuzzleHttp;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use App\Models\SmsSchedule;
use Illuminate\Support\Facades\DB;

class SendQueue extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $date = date_create(date('Y-m-d H:i:s'));
        date_sub($date, date_interval_create_from_date_string("1 days"));
        $messages = MessageQueueLog::where('created_at', 'like', '%' . trim(date_format($date, "Y-m-d")) . '%')
            ->select(DB::raw('routingKey, count(1) as count'))
            ->groupBy('routingKey')
            ->get();

        $data = $messages->map(function ($message) use (& $data) {
            return [
                'routing_key' => $message->routingKey,
                'quantity' => $message->count
            ];
        })->toArray();

        $result = [
            'service_name' => 'teko.sale',
            'sent' => $data,
            'received' => [[
                'routing_key' => '',
                'quantity' => 0
            ]],
        ];

        try {
            $url = 'https://msgqueue-checksum.appspot.com/api/message_log/';
            $client = new GuzzleHttp\Client([
                'headers' => ['Content-Type' => 'application/json']
            ]);

            $response = $client->post($url,
                ['body' => json_encode(
                    $result
                )]
            );
        } catch (RequestException $e) {
            return false;
        }
    }
}
