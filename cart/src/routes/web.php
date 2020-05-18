<?php

// Checkouts
$route = env('PACKAGE_ROUTE', '').'/checkouts/';
$controller = 'Increment\Marketplace\Http\CheckoutController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller."test");

// Checkout Items
$route = env('PACKAGE_ROUTE', '').'/checkout_items/';
$controller = 'Increment\Marketplace\Http\CheckoutItemController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller."test");