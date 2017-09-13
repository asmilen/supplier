<?php

namespace App\Jobs;

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

    protected $productSupplier;
    protected $type;
    protected $user;

    /**
     * Create a new job instance.
     *
     * @param ProductSupplier $productSupplier
     * @param $type
     * @param $user
     */
    public function __construct(ProductSupplier $productSupplier, $type, $user)
    {
        $this->productSupplier = $productSupplier;
        $this->type = $type;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Sentinel::login($this->user);
        if ($this->type == 0){
            $this->productSupplier->offProductToMagento();
        }else{
            $this->productSupplier->onProductToMagento();
        }
    }
}
