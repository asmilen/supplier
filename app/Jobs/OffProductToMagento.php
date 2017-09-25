<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductSupplier;
use Sentinel;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class OffProductToMagento implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $product;
    protected $type;
    protected $user;
    protected $regionId;

    /**
     * Create a new job instance.
     *
     * @param Product $product
     * @param $type
     * @param $user
     * @param $regionId
     */
    public function __construct(Product $product, $type, $user, $regionId)
    {
        $this->product = $product;
        $this->type = $type;
        $this->user = $user;
        $this->regionId = $regionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->type == 0){
            Sentinel::login($this->user);
            $this->product->offProductToMagento($this->regionId);
        }elseif($this->type == 2){
            $this->product->offProductToMagento($this->regionId);
        }else{
            Sentinel::login($this->user);
            $this->product->onProductToMagento($this->regionId);
        }
    }
}
