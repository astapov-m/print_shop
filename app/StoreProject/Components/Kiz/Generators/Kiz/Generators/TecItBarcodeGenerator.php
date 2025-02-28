<?php

namespace App\StoreProject\Components\Kiz\Generators\Kiz\Generators;

use App\StoreProject\Components\Kiz\Generators\Kiz\Generators\Interfaces\KizGeneratorInterface;
use Illuminate\Support\Facades\Http;

class TecItBarcodeGenerator implements KizGeneratorInterface
{
    public function generateKiz(string $data): ?string
    {
        try {
            $urlencode = urlencode($data);
            return Http::get("https://barcode.tec-it.com/barcode.ashx?data={$urlencode}&code=DataMatrix&dmsize=Default")->body();
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
