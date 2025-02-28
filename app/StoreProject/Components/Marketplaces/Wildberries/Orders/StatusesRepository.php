<?php

namespace App\StoreProject\Components\Marketplaces\Wildberries\Orders;

use App\StoreProject\Clients\WbV3;

class StatusesRepository
{
    private $status_repository;

    public function __construct(private WbV3 $client){}

    public function __get(string $name)
    {
        return $this->$name;
    }

    public function getStatuses(array $orders_id = []){
        $this->status_repository = $this->client->post('orders/status',['orders' => $orders_id]);
        return $this;
    }

    public static function getStatusesStatic(array $orders_id = []){
        $repository = new self(app(WbV3::class));
        return $repository->getStatuses($orders_id);
    }
}
