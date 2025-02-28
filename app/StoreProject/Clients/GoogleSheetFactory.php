<?php

namespace App\StoreProject\Clients;

use Google\Client;
use Google\Service\Sheets;

class GoogleSheetFactory
{
    public static function getClient(){
        $client = new Client();
        $client->setScopes([Sheets::SPREADSHEETS]);
        $client->setAuthConfig(storage_path('app/private/google_sheet_keys.json'));
        $client->setAccessType('offline');
        return $client;
    }
}
