<?php

namespace App\Console\Commands;

use App\Models\LogMessageQueue;
use App\Models\MessageQueueLog;
use Carbon\Carbon;
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
        $from = Carbon::yesterday('Asia/Ho_Chi_Minh')->setTimezone('UTC')->format('Y-m-d H:i:s');
        $to = Carbon::now('Asia/Ho_Chi_Minh')->setTime(0,0,0)->setTimezone('UTC')->format('Y-m-d H:i:s');
        $messages = MessageQueueLog::where('created_at', '>=', $from)
            ->where('created_at', '<', $to)
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
            $url = env('MESSAGE_QUEUE_URL');
            $client = new GuzzleHttp\Client([
                'headers' => ['Content-Type' => 'application/json']
            ]);

            $response = $client->post($url,
                ['body' => json_encode(
                    $result
                )]
            );

            \Log::info('post_data_message_queue: ' . json_encode($result));
            
        } catch (RequestException $e) {
            return false;
        }
    }
}
