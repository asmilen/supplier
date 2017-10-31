<?php

namespace App\Console\Commands;

use App\Models\Attribute;
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
        DB::table('attributes')->delete();

        $magentoAttributes = DB::connection('magento')->table('eav_attribute')->get();

        foreach ($magentoAttributes as $magentoAttribute)
        {
            $attribute = Attribute::forceCreate([
                'slug' => $magentoAttribute->attribute_code,
                'backend_type' => $magentoAttribute->backend_type,
                'frontend_input' => $magentoAttribute->frontend_input ? $magentoAttribute->frontend_input : '',
                'name' => $magentoAttribute->frontend_label ? $magentoAttribute->frontend_label : '',
                'id' => $magentoAttribute->attribute_id,
            ]);
        }
    }
}
