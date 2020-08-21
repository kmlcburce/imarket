<?php

// Checkouts
$route = env('PACKAGE_ROUTE', '').'/deliveries/';
$controller = 'Increment\Imarket\Delivery\Http\DeliveryController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'my_deliveries', $controller."myDeliveries");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller."test");