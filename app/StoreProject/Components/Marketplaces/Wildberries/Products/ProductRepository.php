<?php

namespace App\StoreProject\Components\Marketplaces\Wildberries\Products;

use App\StoreProject\Clients\WbV2;
use Carbon\Carbon;

class ProductRepository
{
    //
    public static function getProducts(){
        $products = [];
        $cursor = ["limit" => 50]; // Начинаем с лимитом 100
        $hasMore = true;

        while ($hasMore) {
            // 🔹 Формируем тело запроса
            $requestData = [
                "settings" => [
                    "cursor" => $cursor,
                    "filter" => [
                        "withPhoto" => -1
                    ]
                ]
            ];

            // 🔹 Отправляем POST-запрос
            $response = WbV2::post('get/cards/list',$requestData);

            if (!$response || empty($response['cards'])) {
                break; // Если данных нет, выходим
            }

            // 🔹 Добавляем полученные товары в общий массив
            $products = array_merge($products, $response['cards']);

            $lastProduct = $response['cursor'];
            $cursor = [
                "limit" => 100,
                "updatedAt" => $lastProduct['updatedAt'],
                "nmID" => $lastProduct['nmID']
            ];

            // 🔹 Проверяем, есть ли ещё товары
            $hasMore = ($response['cursor']['total'] >= $cursor['limit']);
            break;
        }

        return $products;
    }
}
