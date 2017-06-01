<?php

namespace App\Console\Commands;

use Google_Client;
use Google_Service_Sheets;
use App\Models\Product;
use App\Models\Category;
use App\Models\Manufacturer;
use Illuminate\Console\Command;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\DefaultServiceRequest;

class ImportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products';

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
        putenv('GOOGLE_APPLICATION_CREDENTIALS='.base_path('client_secret.json'));

        $client = new Google_Client();

        $client->useApplicationDefaultCredentials();

        $client->setApplicationName('Import Products');

        $client->addScope(Google_Service_Sheets::SPREADSHEETS);

        if ($client->isAccessTokenExpired()) {
            $client->refreshTokenWithAssertion();
        }

        $accessToken = $client->fetchAccessTokenWithAssertion()["access_token"];

        ServiceRequestFactory::setInstance(
            new DefaultServiceRequest($accessToken)
        );

        $spreadsheetId = '1l_KBcSVDV7HN6rDRN6jPI0F5VCXZTKlg-foUXDACpRk';

        $service = new Google_Service_Sheets($client);

        $response = $service->spreadsheets_values->get($spreadsheetId, 'products!A:C');

        $rows = $response->getValues();

        array_shift($rows);

        $failed = 0;

        $exists = 0;

        $count = 0;

        foreach ($rows as $row) {
            $category = Category::where('name', $row[0])->first();

            $manufacturer = Manufacturer::where('name', $row[1])->first();

            if (! $category || ! $manufacturer) {
                ++$failed;

                continue;
            }

            $product = Product::where('name', $row[2])
                // ->where('category_id', $category->id)
                // ->where('manufacturer_id', $manufacturer->id)
                ->first();

            if ($product) {
                ++$exists;

                continue;
            }

            Product::forceCreate([
                'category_id' => $category->id,
                'manufacturer_id' => $manufacturer->id,
                'name' => $row[2],
                'status' => 0,
            ]);

            ++$count;
        }

        $this->line($count.' imported, '.$failed.' failed, '.$exists.' exists.');
    }
}
