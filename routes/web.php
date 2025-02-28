<?php

use Illuminate\Support\Facades\Route;

Route::get('barcode/{barcode}',[\App\Http\Controllers\BarcodeController::class,'barcode']);
Route::get('order-print/{order_id}',[\App\Http\Controllers\BarcodeController::class,'handler']);
Route::get('supply-print/{supply}',[\App\Http\Controllers\BarcodeController::class,'supply']);

