<?php

namespace Increment\Imarket\Wishlist;

use Illuminate\Support\ServiceProvider;

class WishlistServiceProvider extends ServiceProvider{

  public function boot(){
    $this->loadMigrationsFrom(__DIR__.'/migrations');
    $this->loadRoutesFrom(__DIR__.'/routes/web.php');
  }

  public function register(){
  }
}