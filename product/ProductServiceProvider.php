<?php

namespace Increment\IMarket\Product;

use Illuminate\Support\ServiceProvider;

class ProductServiceProvider extends ServiceProvider{

  public function boot(){
    $this->loadMigrationsFrom(__DIR__.'/migrations');
    $this->loadRoutesFrom(__DIR__.'/routes/web.php');
  }

  public function register(){
  }
}