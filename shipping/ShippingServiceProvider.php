<?php

namespace Increment\IMarket\Shipping;

use Illuminate\Support\ServiceProvider;

class ShippingServiceProvider extends ServiceProvider{

  public function boot(){
    $this->loadMigrationsFrom(__DIR__.'/migrations');
    $this->loadRoutesFrom(__DIR__.'/routes/web.php');
  }

  public function register(){
  }
}