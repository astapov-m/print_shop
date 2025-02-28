<?php

namespace App\StoreProject\Components\Marketplaces\Wildberries\Orders;

use App\StoreProject\Clients\WbV3;
use Carbon\Carbon;

class OrdersRepository
{
    private array $order_response;
    public function __construct(private WbV3 $client){}

    public function __get(string $name)
    {
        return $this->$name;
    }

    /**
     * @return array
     */
    public function getOrderResponse(): array
    {
        return $this->order_response;
    }

    public static function getOrdersStatic(int $limit = 1000 , int $next = 0 , int $dateFrom = null , int $dateTo = null) : OrdersRepository
    {
        $repository = new self(app(WbV3::class));
        return $repository->getOrders($limit,$next,$dateFrom,$dateTo);
    }

    public static function getNewOrdersStatic(){
        $repository = new self(app(WbV3::class));
        return $repository->getNewOrders();
    }

    public function getNewOrders(){
        $this->order_response = $this->client->get('orders/new');
        return $this;
    }

    public function getOrders(int $limit = 1000 , int $next = 0 , int $dateFrom = null , int $dateTo = null) : OrdersRepository
    {
        if (is_null($dateFrom)){
            $dateFrom = Carbon::now()->subDays(7)->timestamp;
        }
        $this->order_response = $this->client->get('orders',self::generateData($limit,$next,$dateFrom,$dateTo));

        return $this;
    }

    private static function generateData(int $limit = 1000 , int $next = 0 , int $dateFrom = null , int $dateTo = null): array
    {
        $data = [
            'limit' => $limit,
            'next' => $next,
        ];

        if(!is_null($dateFrom)){
            $data['dateFrom'] = $dateFrom;
        }

        if(!is_null($dateTo)){
            $data['dateTo'] = $dateTo;
        }

        return $data;
    }

    public function addStatuses(): void
    {
        $statuses = StatusesRepository::getStatusesStatic($this->getOrdersId())->status_repository;
        foreach ($this->order_response['orders'] as $key => $order){
            $this->order_response['orders'][$key]['supplierStatus'] = $statuses['orders'][$key]['supplierStatus'];
            $this->order_response['orders'][$key]['wbStatus'] = $statuses['orders'][$key]['supplierStatus'];
        }
    }

    private function getOrdersId(): array
    {
        $ordersId = [];
        //print_r($this->order_response);
        foreach ($this->order_response['orders'] as $order) {
            $ordersId[] = $order['id'];
        }
        return $ordersId;
    }

    public static function getOrdersStickersStatic(array $orders_id){
        $repository = new self(app(WbV3::class));
        return $repository->getOrdersStickers($orders_id);
    }

    public function getOrdersStickers(array $orders_id){
        $res = $this->client->post('orders/stickers?type=png&width=40&height=30', ['orders'=>$orders_id]);
        return $res['stickers'];
    }
}
