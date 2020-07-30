<?php

namespace Increment\Imarket\Location;

use Illuminate\Support\ServiceProvider;

class LocationServiceProvider extends ServiceProvider{

  public function boot(){
    $this->loadMigrationsFrom(__DIR__.'/migrations');
    $this->loadRoutesFrom(__DIR__.'/routes/web.php');
  }

  public function register(){
  }
}