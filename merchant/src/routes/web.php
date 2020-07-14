<?php

// Merchants
$route = env('PACKAGE_ROUTE', '').'/merchants/';
$controller = 'Increment\Imarket\Merchant\Http\MerchantController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'retrieve_all', $controller."retrieveAll");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller."test");

$route = env('PACKAGE_ROUTE', '').'/merchant_locations/';
$controller = 'Increment\Imarket\Merchant\Http\MerchantLocationController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");