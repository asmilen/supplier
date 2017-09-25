<?php

namespace App\Console\Commands;

use App\Jobs\OffProductToMagento;
use GuzzleHttp;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use App\Models\SmsSchedule;
use App\Models\ProductSupplier;
use App\Models\Product;
use Sentinel;

class UpdateStatusToMagento extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:updateStatusToMagento';

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
        try {
            $productSuppliers = ProductSupplier::where('region_id', '>', 0)
                ->where('status', '!=', 1)
                ->orWhere('state', 0)
                ->get();

            foreach ($productSuppliers as $productSupplier){
                $product = Product::findOrFail($productSupplier->product_id);
                dispatch(new OffProductToMagento($product, 2, 0, $productSupplier->region_id));
            }
        } catch (RequestException $e) {
            return false;
        }
    }
}
