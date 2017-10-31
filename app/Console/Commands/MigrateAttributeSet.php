<?php

namespace App\Console\Commands;

use App\Models\Attribute;
use App\Models\Category;
use Illuminate\Console\Command;
use DB;

class MigrateAttributeSet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attribute:migrate';

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
        $this->line('Deleting Attribute and Category Attribute Table');

        DB::table('attributes')->delete();
        DB::table('attribute_category')->delete();

        $this->line('Deleted Attribute and Category Attribute Table');

        $this->line('Migrating Attributes');

        $supplierBackendTypes = ['int','decimal','varchar','text'];
        $supplierFrontendInput = ['select','multiselect','text','textarea'];

        $magentoAttributes = DB::connection('magento')->table('eav_attribute')->get();

        foreach ($magentoAttributes as $magentoAttribute)
        {
             Attribute::forceCreate([
                'slug' => $magentoAttribute->attribute_code,
                'backend_type' => in_array($magentoAttribute->backend_type,$supplierBackendTypes) ? $magentoAttribute->backend_type : 'varchar',
                'frontend_input' => in_array($magentoAttribute->frontend_input,$supplierFrontendInput) ? $magentoAttribute->frontend_input : 'text',
                'name' => $magentoAttribute->frontend_label ? $magentoAttribute->frontend_label : '',
                'id' => $magentoAttribute->attribute_id,
            ]);
        }

        $this->line('Migrated Attributes');

        $this->line('Migrating Category Attribute');

        $magentoAttributeSets = DB::connection('magento')->table('eav_attribute_sets')->get();

        foreach($magentoAttributeSets as $magentoAttributeSet)
        {
            $category = Category::where('magento_attribute_set_id',$magentoAttributeSet->attribute_set_id)->first();
            $category->attributes()->save($magentoAttributeSet->attribute_id);
        }

        $this->line('Migrated Category Attribute');

        $this->line('Migrating Product Attribute');

        $magentoBackendTypes = ['int','decimal','varchar','text','datetime'];

        $success = 0 ; $errors = 0;

        foreach ($magentoBackendTypes as $magentoBackendType)
        {
            $productAttributes = DB::connection('magento')->table('catalog_product_entity_' . $magentoBackendType)->get();
            foreach ($productAttributes as $productAttribute)
            {
                try {
                    $product = Product::where('sku',$productAttributes->sku)->first();
                    $attribute = Attribute::find($productAttribute->attribute_id);
                    $product->updateAttribute($attribute,$productAttribute->value);
                    $success++;
                } catch (\Exception $ex) {
                    \Log::error($ex);
                    $errors++;
                }
            }
        }

        $this->line('Migrated Product Attribute: ' . $success . ' success ' . $errors . ' errors');
    }
}
