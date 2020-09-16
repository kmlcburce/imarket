<?php

// Coupons
$route = env('PACKAGE_ROUTE', '').'/rewards/';
$controller = 'Increment\Imarket\Reward\Http\RewardController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'retrieve_details', $controller."retrieveDetails");
Route::post($route.'search', $controller."search");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller."test");