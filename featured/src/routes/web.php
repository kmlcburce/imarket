<?php

$route = env('PACKAGE_ROUTE', '').'/featureds/';
$controller = 'Increment\Imarket\Featured\Http\FeaturedController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::post($route.'add_featured_product', $controller."featuredProduct");
Route::get($route.'test', $controller."test");
