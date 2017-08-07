<?php

namespace App\Console\Commands;

use App\Models\ProductSupplier;
use Carbon\Carbon;
use Sentinel;
use Illuminate\Console\Command;
use Mockery\Exception;

class MigrateValidTimeProductSupplier extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'valid-time:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add price valid time for table product supplier';

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
        //
        $this->info('Start: ' . Carbon::now());

        $user = Sentinel::findById(1);
        Sentinel::login($user);

        $productSuppliers = ProductSupplier::all();

        foreach( $productSuppliers as $productSupplier)
        {
            try{
                if ($productSupplier->supplier && $productSupplier->supplier->price_active_time)
                {
                    $productSupplier->from_date = Carbon::now();
                    $productSupplier->to_date = Carbon::now()->addHours($productSupplier->supplier->price_active_time);
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
