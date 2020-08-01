<?php

$route = env('PACKAGE_ROUTE', '').'/locations/';
$controller = 'Increment\Imarket\Location\Http\LocationController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");