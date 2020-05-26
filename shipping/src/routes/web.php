<?php

// Shipping Addresses
$route = env('PACKAGE_ROUTE', '').'/shipping_addresses/';
$controller = 'Increment\Imarket\Shipping\Http\ShippingAddressController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller."test");

// Shippings
$route = env('PACKAGE_ROUTE', '').'/shippings/';
$controller = 'Increment\Imarket\Shipping\Http\ShippingController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller."test");

// Shipping Rates
$route = env('PACKAGE_ROUTE', '').'/shipping_rates/';
$controller = 'Increment\Imarket\Shipping\Http\ShippingRateController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller."test");