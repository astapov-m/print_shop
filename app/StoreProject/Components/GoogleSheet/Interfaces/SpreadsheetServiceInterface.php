<?php

namespace App\StoreProject\Components\GoogleSheet\Interfaces;

interface SpreadsheetServiceInterface
{
    public function getValues(string $spreadsheetId, string $range): array;
    public function updateValues(string $spreadsheetId, string $range, array $values): void;
}
