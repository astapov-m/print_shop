<?php

namespace App\StoreProject\Clients;

use Illuminate\Support\Facades\Http;

class WbV2
{
    private static function query()
    {
        return Http::withHeaders([
            'Authorization' => env('WB_API_KEY'),
            'Content-Type' => 'application/json'
        ]);
    }

    public static function get(string $path,array $data = []) : ?array
    {
        $res = self::query()->get(env('WILDBERRIES_API_V2_URL').$path,$data);
        return $res->json();
    }

    public static function post(string $path,array $data = []) : ?array
    {
        return self::query()->post(env('WILDBERRIES_API_V2_URL').$path,$data)->json();
    }
}
