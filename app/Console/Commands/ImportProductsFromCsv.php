<?php

namespace App\Console\Commands;

use Excel;
use App\Models\Product;
use App\Models\Category;
use App\Models\Manufacturer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ImportProductsFromCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from CSV';

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
        $total = 0;

        $failed = 0;

        $counts = [];

        Excel::load(base_path('products.csv'))->each(function (Collection $product) use (&$total, &$failed, &$counts) {
            $category = Category::find($product['category_id']);

            if (! $category) {
                \Log::warning('Category '.$product['category_id'].' not exists in Product '.json_encode($product));

                ++$failed;

                return;
            }

            $manufacturer = Manufacturer::find($product['manufacturer_id']);

            if (! $manufacturer) {
                \Log::warning('Manufacturer '.$product['manufacturer_id'].' not exists in Product '.json_encode($product));

                ++$failed;

                return;
            }

            if (isset($counts[$category->id][$manufacturer->id])) {
                ++$counts[$category->id][$manufacturer->id];
            } else {
                $counts[$category->id][$manufacturer->id] = 1001;
            }

            ++$total;

            Product::forceCreate([
                'id' => $product['id'],
                'category_id' => $category->id,
                'manufacturer_id' => $manufacturer->id,
                'name' => $product['name'],
                'code' => $counts[$category->id][$manufacturer->id],
                'old_sku' => $product['sku'],
                'sku' => $category->code.'-'.$manufacturer->code.'-'.$counts[$category->id][$manufacturer->id],
                'status' => true,
            ]);
        });

        $this->info($total.' products imported.');

        if ($failed > 0) {
            $this->warn($failed.' products failed. Check log file for details.');
        }
    }
}
