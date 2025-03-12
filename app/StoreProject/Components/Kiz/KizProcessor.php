<?php

namespace App\StoreProject\Components\Kiz;

use App\StoreProject\Components\GoogleSheet\Interfaces\SpreadsheetServiceInterface;
use App\StoreProject\Components\Kiz\Generators\Kiz\Generators\Interfaces\KizGeneratorInterface;
use App\StoreProject\Components\Marketplaces\Wildberries\Orders\OrdersRepository;
use Illuminate\Support\Facades\File;

class KizProcessor
{
    const kiz_types_map = [
        'A' => ['Худи', 'Зипка'],
        'B' => ['Футболка', 'Лонгслив']
    ];
    public function __construct(private SpreadsheetServiceInterface $spreadsheetService, private KizGeneratorInterface $kizGenerator){}

    public function getKiz(string $name, string $size, string $orderId, string $spreadsheetId, string $color, int $error = 0): ?array
    {
        $error++;
        $size = strtok($size, "/");

        $lizList = null;
        $rangeKiz = '';
        foreach (self::kiz_types_map as $key => $kiz_type) {
            $pattern = '/\b(' . implode('|', $kiz_type) . ')\b/ui';

            preg_match($pattern, $name, $matches);
            $lizList = $matches[1] ?? null;

            if ($lizList){
                if ($key == 'B') {
                    $rangeKiz = str_replace(" ", "", $lizList . $color . $size);
                }else{
                    $rangeKiz = str_replace(" ", "", $lizList . $size);
                }
                break;
            }
        }


        if ($lizList) {

            $existingDataKIZ = $this->spreadsheetService->getValues($spreadsheetId, $rangeKiz);

            foreach ($existingDataKIZ as $key => $subArray) {
                if ($this->shouldSkipRow($subArray)) {
                    continue;
                }

                $codeString = implode(";", $subArray);
                $kizA = substr($codeString, 2, 14);
                $kizB = substr($codeString, 18, 13);
                $barcodeData = $this->kizGenerator->generateKiz($codeString);

                if (!$barcodeData) {
                    return ($error < 4) ? $this->getKiz($name, $size, $orderId, $spreadsheetId, $color, $error) : null;
                }
                $errorHash = hash_file('sha256', storage_path('app/public/error_kiz.png'));
                $newHash = hash('sha256', $barcodeData);
                if ($errorHash == $newHash) {
                    sleep(5);
                    return ($error < 4) ? $this->getKiz($name, $size, $orderId, $spreadsheetId, $color, $error) : null;
                }

                File::put(storage_path("app/public/wb/kiz/$orderId.png"), $barcodeData);
                var_dump($kizA);
                while (count($existingDataKIZ[$key]) < 4) {
                    $existingDataKIZ[$key][] = "";
                }

                $existingDataKIZ[$key][] = "Задание-" . $orderId;

                OrdersRepository::addOrderKizStatic($orderId, $codeString);

                $this->spreadsheetService->updateValues($spreadsheetId, $rangeKiz, $existingDataKIZ);

                return [$kizA, $kizB];
            }
        }

        return null;
    }

    public function getNewKizImage(string $name, string $size, string $orderId, string $spreadsheetId, string $color, int $error = 0): ?array
    {
        $error++;
        $size = strtok($size, "/");

        $lizList = null;
        $rangeKiz = '';
        foreach (self::kiz_types_map as $key => $kiz_type) {
            $pattern = '/\b(' . implode('|', $kiz_type) . ')\b/ui';

            preg_match($pattern, $name, $matches);
            $lizList = $matches[1] ?? null;

            if ($lizList){
                if ($key == 'B') {
                    $rangeKiz = str_replace(" ", "", $lizList . $color . $size);
                }else{
                    $rangeKiz = str_replace(" ", "", $lizList . $size);
                }
                break;
            }
        }


        if ($lizList) {

            $existingDataKIZ = $this->spreadsheetService->getValues($spreadsheetId, $rangeKiz);

            foreach ($existingDataKIZ as $key => $subArray) {
                if ($this->shouldSkipRowById($subArray, $orderId)) {
                    continue;
                }

                $filteredArray = array_filter($subArray, function ($value) {
                    return $value != '';
                });
                array_pop($filteredArray);

                $codeString = implode(";", $filteredArray);

                $barcodeData = $this->kizGenerator->generateKiz($codeString);

                if (!$barcodeData) {
                    return ($error < 4) ? $this->getKiz($name, $size, $orderId, $spreadsheetId, $color, $error) : null;
                }

                File::put(storage_path("app/public/wb/kiz/$orderId.png"), $barcodeData);
                return null;
            }
        }
        return null;
    }

    private function shouldSkipRow(array $subArray): bool
    {
        foreach ($subArray as $value) {
            if (strpos($value, "Задание") !== false) {
                return true;
            }
        }
        return false;
    }

    private function shouldSkipRowById(array $subArray, $orderId): bool
    {
        foreach ($subArray as $value) {
            if ($value == "Задание-$orderId") {
                return true;
            }
        }
        return false;
    }

}
