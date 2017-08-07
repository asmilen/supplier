<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductSupplier;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EmailAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email to administration about product about to expire price valid time';

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
        $expired_time = Carbon::now()->addDay();
        
        $productSuppliers = ProductSupplier::where('to_date', '<=', $expired_time)->get();

        if ($productSuppliers) {
            $managers = config('teko.manager_emails');

            Mail::send('emails.alert', ['products' => $productSuppliers], function ($message) use ($managers) {
                $message->from('supplier-tool@teko.vn', 'Supplier Tool');

                $message->subject('Cảnh báo sản phẩm hết hạn hiệu lực giá');

                $message->to($managers['to']);

                $message->cc($managers['cc']);
            });
        }
    }
}
