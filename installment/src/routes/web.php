<?php

// installments
$route = env('PACKAGE_ROUTE', '').'/installments/';
$controller = 'Increment\Imarket\Installment\Http\InstallmentController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller."test");

// installment requests
$route = env('PACKAGE_ROUTE', '').'/installment_requests/';
$controller = 'Increment\Imarket\Installment\Http\InstallmentRequestController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller."test");