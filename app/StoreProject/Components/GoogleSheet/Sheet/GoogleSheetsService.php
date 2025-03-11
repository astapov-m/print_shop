<?php

namespace App\StoreProject\Components\GoogleSheet\Sheet;

use App\StoreProject\Components\GoogleSheet\Interfaces\SpreadsheetServiceInterface;
use Google\Service\Sheets;
use Google\Service\Sheets\ClearValuesRequest;
use Google\Service\Sheets\ValueRange;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_ClearValuesRequest;

class GoogleSheetsService implements SpreadsheetServiceInterface
{
    public function __construct(private Sheets $service){}

    public function getValues(string $spreadsheetId, string $range, bool $header = true): array
    {
        $response = $this->service->spreadsheets_values->get($spreadsheetId, $range);
        $result = $response->getValues();
        if (!$header){
            array_shift($result);
        }
        return  $result ?? [];
    }

    public function updateValues(string $spreadsheetId, string $range, array $values): void
    {
        $body = new ValueRange(['values' => $values]);
        $params = ['valueInputOption' => 'USER_ENTERED'];
        $this->service->spreadsheets_values->update($spreadsheetId, $range, $body, $params);
    }

    public function clearSheetExceptFirstRow(string $spreadsheetId, string $sheetName): void
    {
        $range = "{$sheetName}!A2:Z";

        $body = new Google_Service_Sheets_ClearValuesRequest();

        $this->service->spreadsheets_values->clear($spreadsheetId, $range, $body);
    }

    public function addValues(string $spreadsheetId, string $range, array $values): void
    {
        $body = new ValueRange(['values' => $values]);
        $params = ['valueInputOption' => 'USER_ENTERED'];
        $this->service->spreadsheets_values->update($spreadsheetId, $range, $body, $params);
    }


    public function updateSize(string $spreadsheetId, int $sheet_id): void
    {
        $spreadsheet = $this->service->spreadsheets->get($spreadsheetId);
        $sheets = $spreadsheet->getSheets();
        $sheet = $sheets[$sheet_id]->getProperties()->getSheetId(); // ID первого листа

        $requests = [
            [
                'updateDimensionProperties' => [
                    'range' => [
                        'sheetId' => $sheet,
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

        $this->service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);
    }
}
