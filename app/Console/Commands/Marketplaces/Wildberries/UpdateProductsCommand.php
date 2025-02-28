<?php

namespace App\Console\Commands\Marketplaces\Wildberries;

use App\StoreProject\Clients\GoogleSheetFactory;
use App\StoreProject\Components\GoogleSheet\Enums\Wildberries\ListIdEnum;
use App\StoreProject\Components\Marketplaces\Wildberries\Products\ProductRepository;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wildberries:update-products-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = GoogleSheetFactory::getClient();
        $service = new Sheets($client);

        $spreadsheetId = env('SPREADSHEET_ID'); // ID вашей Google таблицы
        $range = 'Wildberries Баркоды'; // Диапазон ячеек, например, 'Sheet1!A1'

        $spreadsheet = $service->spreadsheets->get($spreadsheetId);
        $sheets = $spreadsheet->getSheets();
        $sheetId = $sheets[ListIdEnum::products->value]->getProperties()->getSheetId(); // ID первого листа

        $requests = [
            [
                'updateDimensionProperties' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'dimension' => 'ROWS', // Изменяем высоту строк
                        'startIndex' => 1, // Индекс строки (1 = вторая строка)
                        'endIndex' => 2000 // Следующая строка (если одна — startIndex + 1)
                    ],
                    'properties' => [
                        'pixelSize' => 200 // Высота в пикселях
                    ],
                    'fields' => 'pixelSize'
                ]
            ]
        ];

        $batchUpdateRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
            'requests' => $requests
        ]);

        $service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $existingData = $response->getValues();
        array_shift($existingData);

        $ids = array_map(function($row) {
            return $row[0];
        }, $existingData);

        $products = ProductRepository::getProducts();

        $data = [];

        foreach ($products as $product) {
            $name = $product['title'];
            $article = $product['vendorCode'];
            $color = collect($product['characteristics'])->firstWhere('id', 14177449)['value'][0] ?? '-' ;
            $image = $product['photos'][0]['big'];
            foreach ($product['sizes'] as $size) {
                if (!in_array($size, $ids)) {
                    $data[] = [
                        'barcode' => $size['skus'][0],
                        'article' => $article,
                        'name' => $name,
                        'size1' => $size['techSize'],
                        'size2' => $size['wbSize'],
                        'color' => $color,
                        'image_link' => $image,
                        'image' => '=IMAGE("'.$image.'"; 4; 200; 200)',
                        'print_link' => env('PRINT_LINK'),"barcode/".$size['skus'][0]
                    ];
                }
            }
        }
        $updatedData = array_merge(array_map('array_values', $data), $existingData); // Соединяем массивы

        $body = new ValueRange(['values' => $updatedData]);
        $params = ['valueInputOption' => 'USER_ENTERED'];
        $service->spreadsheets_values->update($spreadsheetId, 'Wildberries Баркоды!A2', $body, $params);
    }
}
