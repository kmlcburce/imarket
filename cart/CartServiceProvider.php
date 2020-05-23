<?php

namespace Increment\Imarket\Cart;

use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider{

  public function boot(){
    $this->loadMigrationsFrom(__DIR__.'/migrations');
    $this->loadRoutesFrom(__DIR__.'/routes/web.php');
  }

  public function register(){
  }
}