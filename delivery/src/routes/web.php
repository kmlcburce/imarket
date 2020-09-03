<?php

// Deliveries
$route = env('PACKAGE_ROUTE', '').'/deliveries/';
$controller = 'Increment\Imarket\Delivery\Http\DeliveryController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'my_deliveries', $controller."myDeliveries");
Route::post($route.'get_delivery_fees', $controller."getDeliveryFees");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller."test");

// Delivery Fees
$route = env('PACKAGE_ROUTE', '').'/delivery_fees/';
$controller = 'Increment\Imarket\Delivery\Http\DeliveryFeeController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller."test");

//delivery fees
$route = env('PACKAGE_ROUTE', '').'/delivery_fees/';
$controller = 'Increment\Imarket\Delivery\Http\DeliveryFeeController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller."test");