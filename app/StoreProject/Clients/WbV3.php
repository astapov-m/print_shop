<?php

namespace App\StoreProject\Clients;

use Illuminate\Support\Facades\Http;

class WbV3
{
    private function query()
    {
        return Http::withHeaders([
            'Authorization' => env('WB_API_KEY'),
            'Content-Type' => 'application/json'
        ]);
    }

    public function get(string $path,array $data = []) : ?array
    {
        $res = $this->query()->get(env('WILDBERRIES_API_V3_URL').$path,$data);
        return $res->json();
    }

    public function post(string $path,array $data = []) : ?array
    {
        return $this->query()->post(env('WILDBERRIES_API_V3_URL').$path,$data)->json();
    }
//
//    public function put(string $path,array $data = []) : ?array
//    {
//        $res = $this->query()->put(env('WILDBERRIES_API_V3_URL').$path,$data);
//        return $res->json();
//    }
//
//    public function patch(string $path,array $data = []) : ?array
//    {
//        $res = $this->query()->patch(env('WILDBERRIES_API_V3_URL').$path,$data);
//        return $res->json();
//    }
}
