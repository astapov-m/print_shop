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

    public function getOrders(int $limit = 1000 , int $next = 0 , int $dateFrom = null , int $dateTo = null)
    {
        if (is_null($dateFrom)){
            $dateFrom = Carbon::now()->subDays(6)->timestamp;
        }
        return $this->client->get('orders',self::generateData($limit,$next,$dateFrom,$dateTo));
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
        $allStatuses = collect();
        $orderIds = $this->getOrdersId();
        $chunkedIds = array_chunk($orderIds, 1000);

        foreach ($chunkedIds as $idsChunk) {
            $statusesResponse = StatusesRepository::getStatusesStatic($idsChunk)->status_repository;
            $statuses = collect($statusesResponse['orders'] ?? []);
            $allStatuses = $allStatuses->merge($statuses);
        }

        foreach ($this->order_response['orders'] as $key => $order) {
            $this->order_response['orders'][$key]['supplierStatus'] =
                $allStatuses->firstWhere('id', $order['id'])['supplierStatus'] ?? null;
        }
    }

    public function getAllOrders(int $dateFrom = null, int $dateTo = null): OrdersRepository
    {
        $allOrders = [];
        $next = 0;

        do {
            $res = $this->getOrders(1000, $next, $dateFrom, $dateTo);
            $orders = $res['orders'] ?? [];
            $allOrders = array_merge($allOrders, $orders);

            $next = $res['next'] ?? 0;
        } while ($next);

        $this->order_response['orders'] = $allOrders;
        $this->order_response['next'] = 0;

        return $this;
    }



    private function getOrdersId(): array
    {
        $ordersId = [];
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

    public function addOrderKiz($orders_id, $kizCode)
    {
        $this->client->put("orders/$orders_id/meta/sgtin",[
            'sgtins' => [
                preg_replace('/[\x00-\x1F\x7F]/u', '', $kizCode)
            ]
        ]);
    }

    public static function addOrderKizStatic($orders_id, $kizCode): void
    {
        $repository = new self(app(WbV3::class));
        $repository->addOrderKiz($orders_id, $kizCode);
    }


}
