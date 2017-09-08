<?php

namespace App\Console\Commands;

use App\Models\ProductSupplier;
use App\Models\SupplierSupportedProvince;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Sentinel;

class MigrateRegionIdProductSupplier extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'region:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add region for table product supplier base on Supplier';

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
        $this->info('Start: ' . Carbon::now());

        $user = Sentinel::findById(1);
        Sentinel::login($user);

        $productSuppliers = ProductSupplier::all();

        foreach( $productSuppliers as $productSupplier)
        {
            try{
                if ($productSupplier->supplier)
                {
                    $regionId = SupplierSupportedProvince::join('provinces','province_id','=','provinces.id')
                        ->where('supplier_id',$productSupplier->supplier->id)
                        ->pluck('provinces.region_id')
                        ->first();
                    $productSupplier->region_id = $regionId;
                    $productSupplier->save();
                }
            }
            catch (Exception $e)
            {
                \Log::error($e->getMessage());
            }
        }

        $this->info('Done: ' . Carbon::now());
    }
}
