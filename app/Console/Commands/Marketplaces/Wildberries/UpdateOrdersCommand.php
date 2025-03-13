<?php

namespace App\Console\Commands\Marketplaces\Wildberries;

use App\StoreProject\Clients\GoogleSheetFactory;
use App\StoreProject\Components\GoogleSheet\Enums\Wildberries\ListEnum;
use App\StoreProject\Components\GoogleSheet\Enums\Wildberries\ListIdEnum;
use App\StoreProject\Components\GoogleSheet\Enums\Wildberries\OrderListEnum;
use App\StoreProject\Components\GoogleSheet\Enums\Wildberries\ProductListEnum;
use App\StoreProject\Components\GoogleSheet\Sheet\GoogleSheetsService;
use App\StoreProject\Components\Kiz\Generators\Kiz\Generators\TecItBarcodeGenerator;
use App\StoreProject\Components\Kiz\KizProcessor;
use App\StoreProject\Components\Marketplaces\Wildberries\Orders\OrdersRepository;
use Google\Service\Sheets;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpdateOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wildberries:update-orders-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

    private GoogleSheetsService $spreadsheetService;

    public function handle()
    {
        $client = GoogleSheetFactory::getClient();
        $service = new Sheets($client);
        $this->spreadsheetService = new GoogleSheetsService($service);


        $spreadsheetId = env('SPREADSHEET_ID');
        $range_orders = ListEnum::order_list->value;


        $this->spreadsheetService->updateSize($spreadsheetId, ListIdEnum::orders->value);


        $old_orders = $this->spreadsheetService->getValues($spreadsheetId, $range_orders, false);


        $ids_old_orders = array_map(function($row) {
            return $row[OrderListEnum::order_id->value];
        }, $old_orders);

        $orders = OrdersRepository::getOrdersStatic();
        $orders->addStatuses();
        $all_confirm_orders = collect($orders->getOrderResponse()['orders'])->where('supplierStatus','confirm')
            ->values()
            ->reverse();

        $all_confirm_orders_id = $all_confirm_orders->pluck('id')->toArray();

        $old_orders_confirm = [];
        foreach ($old_orders as $order) {
            if (in_array($order[OrderListEnum::order_id->value], $all_confirm_orders_id)) {
                if ($order[OrderListEnum::supply->value] != $all_confirm_orders->firstWhere('id', $order[OrderListEnum::order_id->value])['supplyId']){
                    $order[OrderListEnum::supply->value] = $all_confirm_orders->firstWhere('id', $order[OrderListEnum::order_id->value])['supplyId'];
                }
                $old_orders_confirm[] = $order;
            }
        }

        $this->updateSupplyList($all_confirm_orders->pluck('supplyId')->unique()->toArray(), $spreadsheetId);


        $orders = collect($orders->getOrderResponse()['orders'])->where('supplierStatus','confirm')->whereNotIn('id',$ids_old_orders)
            ->take(20)
            ->values()
            ->reverse();

        $newOrderIds = $orders->pluck('id')->toArray();

        if (count($newOrderIds) != 0) {
            $stickers = collect(OrdersRepository::getOrdersStickersStatic($newOrderIds));

            foreach ($stickers as $sticker) {
                File::put(storage_path('app/public/wb/barcodes/'."{$sticker['orderId']}.png"), base64_decode($sticker['file']));
            }
        }



        $range_barcodes = ListEnum::product_list->value;
        $products_list = $this->spreadsheetService->getValues($spreadsheetId, $range_barcodes, false);


        $barcodeGenerator = new TecItBarcodeGenerator();
        $kizProcessor = new KizProcessor($this->spreadsheetService, $barcodeGenerator);


        $data = [];
        foreach ($orders as $order) {
            $sticker = $stickers->firstWhere('orderId', $order['id']);
            $label = $sticker['partA'].'-'.$sticker['partB'];

            $barcode = $order['skus'][0];
            $product = array_filter($products_list, function ($row) use ($barcode) {
                return isset($row[ProductListEnum::barcode->value]) && $row[ProductListEnum::barcode->value] == $barcode;
            });
            $product = array_values($product)[0];

            $name = $product[ProductListEnum::name->value];
            $size = $product[ProductListEnum::sizeA->value];
            $color = $product[ProductListEnum::color->value];

            $kiz = $kizProcessor->getKiz($name, $size, $order['id'], $spreadsheetId, $color);
            sleep(1);

            $data[] = [
                $order['id'],
                $barcode,
                $label,
                $order['article'],
                $name.' '.$size,
                '=IMAGE("'.$product[ProductListEnum::photo_link->value].'"; 4; 200; 200)',
                $order['supplyId'],
                env('PRINT_LINK').'order-print/'.$order['id'],
                is_null($kiz) ? '-' : $kiz[0],
                is_null($kiz) ? '-' : $kiz[1],
            ];
        }

        foreach ($old_orders_confirm as $key => $item){
            $barcode = $item[1];

            $product = array_filter($products_list, function ($row) use ($barcode) {
                return isset($row[0]) && $row[0] == $barcode;
            });

            $product = array_values($product)[0];

            $old_orders_confirm[$key][OrderListEnum::photo->value] = '=IMAGE("'.$product[6].'"; 4; 200; 200)';

            if ($old_orders_confirm[$key][OrderListEnum::kizA->value] == '-' && in_array($item[0], $all_confirm_orders_id)){
                $kiz = $kizProcessor->getKiz($product[ProductListEnum::name->value], $product[ProductListEnum::sizeA->value], $item[0], $spreadsheetId, $product[ProductListEnum::color->value]);
                if (!is_null($kiz)){
                    $old_orders_confirm[$key][OrderListEnum::kizA->value] = $kiz[0];
                    $old_orders_confirm[$key][OrderListEnum::kizB->value] = $kiz[1];
                }
            }
            if ($old_orders_confirm[$key][OrderListEnum::kizA->value] != '-'){
                $errorHash = hash_file('sha256', storage_path('app/public/error_kiz.png'));
                $thisHash = hash_file('sha256', storage_path("app/public/wb/kiz/$item[0].png"));
                if ($errorHash == $thisHash){
                    $kizProcessor->getNewKizImage($product[ProductListEnum::name->value], $product[ProductListEnum::sizeA->value], $item[0], $spreadsheetId, $product[ProductListEnum::color->value]);
                }
            }
        }

        $updatedData = array_merge($data, $old_orders_confirm);
        usort($updatedData, function($a, $b) {
            return strcmp($a[OrderListEnum::article->value], $b[OrderListEnum::article->value]); // Сравнение строк по 4-му ключу (индекс 3)
        });


        $this->spreadsheetService->clearSheetExceptFirstRow($spreadsheetId, $range_orders);

        $this->spreadsheetService->updateValues($spreadsheetId, "$range_orders!A2", $updatedData);
    }

    private function updateSupplyList(array $suppliers, string $spreadsheetId): void
    {
        $range_supply = ListEnum::supply_list->value;
        $updatedDataSupply = [];
        foreach ($suppliers as $supply){
            $updatedDataSupply[] = [$supply, env('PRINT_LINK').'supply-print/'.$supply];
        }

        $this->spreadsheetService->clearSheetExceptFirstRow($spreadsheetId, $range_supply);

        $this->spreadsheetService->updateValues($spreadsheetId,"$range_supply!A2",$updatedDataSupply);
    }

}
