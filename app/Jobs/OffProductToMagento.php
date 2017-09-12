<?php

namespace App\Jobs;

use App\Models\Supplier;
use Sentinel;
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
    protected $user;

    /**
     * Create a new job instance.
     *
     * @param Supplier $supplier
     * @param $type
     * @param $user
     */
    public function __construct(Supplier $supplier, $type, $user)
    {
        $this->supplier = $supplier;
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
            $this->supplier->offProductToMagento();
        }else{
            $this->supplier->onProductToMagento();
        }
    }
}
