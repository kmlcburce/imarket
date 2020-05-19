<?php

// Shipping Addresses
$route = env('PACKAGE_ROUTE', '').'/shipping_addresses/';
$controller = 'Increment\IMarket\Shipping\Http\ShippingAddressController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller."test");