<?php

namespace Increment\Imarket\Rental;

use Illuminate\Support\ServiceProvider;

class RentalServiceProvider extends ServiceProvider{

  public function boot(){
    $this->loadMigrationsFrom(__DIR__.'/migrations');
    $this->loadRoutesFrom(__DIR__.'/routes/web.php');
  }

  public function register(){
  }
}