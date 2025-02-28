<?php

namespace App\StoreProject\Components\Kiz;

use App\StoreProject\Components\GoogleSheet\Interfaces\SpreadsheetServiceInterface;
use App\StoreProject\Components\Kiz\Generators\Kiz\Generators\Interfaces\KizGeneratorInterface;
use Illuminate\Support\Facades\File;

class KizProcessor
{
    const kiz_types = ['Худи', 'Зипка'];
    public function __construct(private SpreadsheetServiceInterface $spreadsheetService, private KizGeneratorInterface $kizGenerator){}

    public function getKiz(string $name, string $size, string $orderId, string $spreadsheetId, int $error = 0): ?array
    {
        $error++;
        $size = strtok($size, "/");
        $pattern = '/\b(' . implode('|', self::kiz_types) . ')\b/ui';

        preg_match($pattern, $name, $matches);
        $lizList = $matches[1] ?? null;

        if ($lizList) {
            $rangeKiz = str_replace(" ", "", $lizList . $size);
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
                    return ($error < 4) ? $this->getKiz($name, $size, $orderId, $spreadsheetId, $error) : null;
                }

                File::put(storage_path("app/public/wb/kiz/$orderId.png"), $barcodeData);

                while (count($existingDataKIZ[$key]) < 4) {
                    $existingDataKIZ[$key][] = "";
                }

                $existingDataKIZ[$key][] = "Задание-" . $orderId;
                $this->spreadsheetService->updateValues($spreadsheetId, $rangeKiz, $existingDataKIZ);

                return [$kizA, $kizB];
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

}
