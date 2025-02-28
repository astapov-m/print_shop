<?php

namespace App\Http\Controllers;


use App\StoreProject\Clients\GoogleSheetFactory;
use Barryvdh\DomPDF\Facade\Pdf;
use Google\Service\Sheets;
use TCPDFBarcode;

class BarcodeController extends Controller
{
    public function handler(string $order_id){

        $client = GoogleSheetFactory::getClient();
        $service = new Sheets($client);

        $spreadsheetId = env('SPREADSHEET_ID'); // ID вашей Google таблицы
        $range = 'Wildberries'; // Диапазон ячеек, например, 'Sheet1!A1'

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $existingData = $response->getValues();
        array_shift($existingData);

        $filteredRows = array_filter($existingData, function ($row) use ($order_id) {
            return isset($row[0]) && $row[0] == $order_id;
        });

        $filteredRows = array_values($filteredRows)[0];
        $barcode = $filteredRows[1];
        $kezA = $filteredRows[8] ?? null;
        $kezB = $filteredRows[9] ?? null;
        $range = 'Wildberries Баркоды'; // Диапазон ячеек, например, 'Sheet1!A1'
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $existingData = $response->getValues();
        array_shift($existingData);

        $filteredRows = array_filter($existingData, function ($row) use ($barcode) {
            return isset($row[0]) && $row[0] == $barcode;
        });

        $barcode_v = new TCPDFBarcode($barcode, 'C128');
        $barcode_img_teg = $barcode_v->getBarcodeHTML(3, 100);

        $filteredRows = array_values($filteredRows)[0];
        $filteredRows[6] = $barcode_img_teg;
        $filteredRows[7] = $order_id;
        $filteredRows[8] = $kezA;
        $filteredRows[9] = $kezB;

        $name = $filteredRows[2];

        $names = ['Худи', 'Зипка']; // Добавьте нужные варианты
        $pattern = '/\b(' . implode('|', $names) . ')\b/ui';

        preg_match($pattern, $name, $matches);
        $kiz_name = $matches[1] ?? null;

        if ($kiz_name == 'Зипка'){
            $kiz_name = 'Зипхуди';
        }

        $filteredRows[10] = $kiz_name;



        $pdf = Pdf::loadView('wb_barcodes_all',compact('filteredRows'),[],'utf-8')->setPaper([0, 0, 290,200]);
        return $pdf->stream();

    }

    public function barcode(string $barcode)
    {
        $client = GoogleSheetFactory::getClient();
        $service = new Sheets($client);

        $spreadsheetId = env('SPREADSHEET_ID'); // ID вашей Google таблицы
        $range = 'Wildberries Баркоды'; // Диапазон ячеек, например, 'Sheet1!A1'
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $existingData = $response->getValues();
        array_shift($existingData);

        $filteredRows = array_filter($existingData, function ($row) use ($barcode) {
            return isset($row[0]) && $row[0] == $barcode;
        });

        $barcode_v = new TCPDFBarcode($barcode, 'C128');
        $barcode_img_teg = $barcode_v->getBarcodeHTML(1.5, 40);

        $filteredRows = array_values($filteredRows)[0];
        $filteredRows[6] = $barcode_img_teg;


        $pdf = Pdf::loadView('wb_barcodes',compact('filteredRows'),[],'utf-8')->setPaper([0, 0, 290,200]);
        return $pdf->stream();
    }

    public function supply(string $supply){

        $client = GoogleSheetFactory::getClient();
        $service = new Sheets($client);

        $spreadsheetId = env('SPREADSHEET_ID'); // ID вашей Google таблицы
        $range = 'Wildberries'; // Диапазон ячеек, например, 'Sheet1!A1'

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $existingData = $response->getValues();
        array_shift($existingData);

        $range_bar = 'Wildberries Баркоды'; // Диапазон ячеек, например, 'Sheet1!A1'
        $response_bar = $service->spreadsheets_values->get($spreadsheetId, $range_bar);
        $existingData_bar = $response_bar->getValues();
        array_shift($existingData_bar);

        $supply_data = [];
        foreach ($existingData as $row) {

            if ($row[6] == $supply){
                $order_id = $row[0];
                $filteredRows = array_filter($existingData, function ($row_fun) use ($order_id) {
                    return isset($row_fun[0]) && $row_fun[0] == $order_id;
                });

                $filteredRows = array_values($filteredRows)[0];
                $barcode = $filteredRows[1];
                $kezA = $filteredRows[8] ?? null;
                $kezB = $filteredRows[9] ?? null;


                $filteredRows = array_filter($existingData_bar, function ($row_fun) use ($barcode) {
                    return isset($row_fun[0]) && $row_fun[0] == $barcode;
                });

                $barcode_v = new TCPDFBarcode($barcode, 'C128');
                $barcode_img_teg = $barcode_v->getBarcodeHTML(3, 100);

                $filteredRows = array_values($filteredRows)[0];
                $filteredRows[6] = $barcode_img_teg;
                $filteredRows[7] = $order_id;
                $filteredRows[8] = $kezA;
                $filteredRows[9] = $kezB;

                $name = $filteredRows[2];

                $names = ['Худи', 'Зипка']; // Добавьте нужные варианты
                $pattern = '/\b(' . implode('|', $names) . ')\b/ui';

                preg_match($pattern, $name, $matches);
                $kiz_name = $matches[1] ?? null;

                if ($kiz_name == 'Зипка'){
                    $kiz_name = 'Зипхуди';
                }

                $filteredRows[10] = $kiz_name;
                $supply_data[] = $filteredRows;
            }

        }

        $pdf = Pdf::loadView('wb_supply_all',compact('supply_data'),[],'utf-8')->setPaper([0, 0, 290,200]);
        return $pdf->stream();

    }
}
