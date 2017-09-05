<?php

namespace App\Jobs;

use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class OffProductToMagento implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $supplier;
    protected $type;

    /**
     * Create a new job instance.
     *
     * @param Supplier $supplier
     * @param $type
     */
    public function __construct(Supplier $supplier, $type)
    {
        $this->supplier = $supplier;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->type == 0){
            $this->supplier->offProductToMagento();
        }else{
            $this->supplier->onProductToMagento();
        }
    }
}
