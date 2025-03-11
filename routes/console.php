<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('wildberries:update-products-command', function () {
    (new \App\Console\Commands\Marketplaces\Wildberries\UpdateProductsCommand())->handle();
})->purpose('Обновление товаров wildberries в товарную матрицу google docs');

Artisan::command('wildberries:update-orders-command', function () {
    (new \App\Console\Commands\Marketplaces\Wildberries\UpdateOrdersCommand())->handle();
})->purpose('Обновление данных по сборочным заданиям и поставкам wildberries в google docs');
