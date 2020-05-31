<?php

namespace Increment\Imarket\Installment;

use Illuminate\Support\ServiceProvider;

class InstallmentServiceProvider extends ServiceProvider{

  public function boot(){
    $this->loadMigrationsFrom(__DIR__.'/migrations');
    $this->loadRoutesFrom(__DIR__.'/routes/web.php');
  }

  public function register(){
  }
}