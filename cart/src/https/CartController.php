<?php

namespace Increment\Imarket\Cart\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Cart\Models\Cart;
class CartController extends APIController
{

  function __construct(){
    $this->model = new Cart();
  }

}
