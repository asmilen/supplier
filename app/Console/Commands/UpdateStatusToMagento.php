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
use DB;

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
            $productOffs = DB::select("SELECT a.id, c.region_id
                      FROM products a
                      join (SELECT 1 as region_id UNION SELECT 2 UNION SELECT 3 ) c 
                      LEFT JOIN
                    (
                    SELECT
                        product_supplier.product_id, product_supplier.region_id, product_supplier.supplier_id
                    FROM
                    product_supplier
                    LEFT JOIN suppliers ON suppliers.id = product_supplier.supplier_id
                    WHERE
                        product_supplier.region_id > 0
                    AND product_supplier.state = 1
                    and suppliers.`status` = 1) b ON a.id = b.product_id and c.region_id = b.region_id
                    WHERE (a.`status` = 0 OR b.product_id is NULL ) AND a.channel LIKE '%2%'");


            foreach ($productOffs as $productSupplier) {
                $product = Product::findOrFail($productSupplier->id);
                dispatch(new OffProductToMagento($product, 2, 0, $productSupplier->region_id));
            }
        } catch (RequestException $e) {
            return false;
        }
    }
}
